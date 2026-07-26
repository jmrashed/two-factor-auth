<?php

namespace Jmrashed\TwoFactorAuth\Http\Controllers;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Hash;
use Jmrashed\TwoFactorAuth\Contracts\TwoFactorAuthenticatable;

use function config;
use function redirect;
use function response;
use function session;
use function trans;
use function view;

class TwoFactorSettingsController extends Controller
{
    /**
     * Show the 2FA settings page.
     */
    public function showSettings(Request $request)
    {
        /** @var Authenticatable&TwoFactorAuthenticatable $user */
        $user = $request->user();

        $qrCodeSvg = null;
        if ($user->hasTwoFactorEnabled() && $user->getTwoFactorMethod() === 'totp') {
            // toQr() already renders the full SVG markup (see SerializesSharedSecret::toQr()) —
            // it must not be re-encoded through another Writer, and BaconQrCode\Renderer\Module\
            // MatrixFactory doesn't even exist in bacon/bacon-qr-code 2.x, so the old code here
            // fatally errored the instant a TOTP user opened the settings page.
            $qrCodeSvg = $user->twoFactorAuth->toQr();
        }

        return view('two-factor::settings', [
            'user' => $user,
            'qrCodeSvg' => $qrCodeSvg,
        ]);
    }

    /**
     * Update the 2FA settings.
     */
    public function updateSettings(Request $request): RedirectResponse|JsonResponse
    {
        /** @var Authenticatable&TwoFactorAuthenticatable $user */
        $user = $request->user();
        $action = $request->input('action');

        // These actions weaken or remove an existing security control, so we
        // require the user to re-prove they are who they say they are before
        // a hijacked session alone can turn 2FA off or rotate backup codes.
        $sensitiveActions = ['disable_totp', 'disable_sms', 'disable_email', 'regenerate_recovery_codes'];

        $status = null;
        $error = null;
        $recoveryCodes = null;

        if (in_array($action, $sensitiveActions, true) && ! $this->currentPasswordConfirmed($request, $user)) {
            $error = trans('two-factor::messages.invalid_password');

            return $this->respond($request, false, $error);
        }

        switch ($action) {
            case 'enable_totp':
                $user->createTwoFactorAuth();
                $user->enableTwoFactorAuth();
                $status = trans('two-factor::messages.totp_enabled');
                break;
            case 'confirm_totp':
                $request->validate([
                    'totp_code' => ['required', 'string', 'size:'.config('two-factor.totp.digits')],
                ]);
                if ($user->confirmTwoFactorAuth($request->input('totp_code'))) {
                    $user->setTwoFactorMethod('totp');
                    $status = trans('two-factor::messages.totp_confirmed');
                } else {
                    $error = trans('two-factor::messages.invalid_totp_code');
                }
                break;
            case 'disable_totp':
                $user->disableTwoFactorAuth();
                $status = trans('two-factor::messages.totp_disabled');
                break;
            case 'enable_sms':
                $request->validate([
                    'phone_number' => ['required', 'string', 'regex:/^\+[1-9]\d{6,14}$/'],
                ]);
                $user->setPhoneNumber($request->input('phone_number'));
                $user->setTwoFactorMethod('sms');
                $status = trans('two-factor::messages.sms_enabled');
                break;
            case 'send_sms_code':
                $request->validate([
                    'phone_number' => ['required', 'string', 'regex:/^\+[1-9]\d{6,14}$/'],
                ]);
                $user->setPhoneNumber($request->input('phone_number'));
                if ($user->sendSmsCode()) {
                    $status = trans('two-factor::messages.sms_code_sent');
                } else {
                    $error = trans('two-factor::messages.sms_send_failed');
                }
                break;
            case 'verify_sms':
                $request->validate([
                    'sms_code' => 'required|string|size:'.config('two-factor.sms.code_length', 6),
                ]);
                if ($user->validateAlternativeTwoFactorCode($request->input('sms_code'), 'sms')) {
                    $user->verifyPhoneNumber();
                    $status = trans('two-factor::messages.sms_verified');
                } else {
                    $error = trans('two-factor::messages.invalid_sms_code');
                }
                break;
            case 'disable_sms':
                $user->setPhoneNumber(''); // Clear phone number
                $user->setTwoFactorMethod('totp'); // Revert to TOTP or no 2FA
                $status = trans('two-factor::messages.sms_disabled');
                break;
            case 'enable_email':
                $user->enableEmailTwoFactor();
                $user->setTwoFactorMethod('email');
                $status = trans('two-factor::messages.email_enabled');
                break;
            case 'send_email_code':
                if ($user->sendEmailCode()) {
                    $status = trans('two-factor::messages.email_code_sent');
                } else {
                    $error = trans('two-factor::messages.email_send_failed');
                }
                break;
            case 'verify_email':
                $request->validate([
                    'email_code' => 'required|string|size:'.config('two-factor.email.code_length', 6),
                ]);
                if ($user->validateAlternativeTwoFactorCode($request->input('email_code'), 'email')) {
                    $status = trans('two-factor::messages.email_verified');
                } else {
                    $error = trans('two-factor::messages.invalid_email_code');
                }
                break;
            case 'disable_email':
                $user->disableEmailTwoFactor();
                $user->setTwoFactorMethod('totp'); // Revert to TOTP or no 2FA
                $status = trans('two-factor::messages.email_disabled');
                break;
            case 'regenerate_recovery_codes':
                $generatedAt = $user->twoFactorAuth->recovery_codes_generated_at;
                $cooldown = (int) config('two-factor.recovery_regeneration_cooldown', 5);

                if ($generatedAt && $generatedAt->copy()->addMinutes($cooldown)->isFuture()) {
                    $error = trans('two-factor::messages.recovery_regeneration_cooldown', ['minutes' => $cooldown]);
                    break;
                }

                $recoveryCodes = $user->generateRecoveryCodes();
                $status = trans('two-factor::messages.recovery_regenerated');
                break;
            default:
                $error = trans('two-factor::messages.invalid_action');
                break;
        }

        return $this->respond($request, $error === null, $error ?? $status, $recoveryCodes);
    }

    /**
     * Verify the "current_password" input against the authenticated user's password.
     */
    protected function currentPasswordConfirmed(Request $request, Authenticatable&TwoFactorAuthenticatable $user): bool
    {
        $request->validate(['current_password' => 'required|string']);

        return Hash::check($request->input('current_password'), $user->getAuthPassword());
    }

    /**
     * Build the response, either as JSON for API/SPA consumers or a redirect with flashed session data.
     */
    protected function respond(Request $request, bool $successful, ?string $message, mixed $recoveryCodes = null): RedirectResponse|JsonResponse
    {
        if ($request->wantsJson()) {
            return response()->json(array_filter([
                'message' => $message,
                'recovery_codes' => $recoveryCodes,
            ], static fn ($value) => $value !== null), $successful ? 200 : 422);
        }

        session()->flash($successful ? 'status' : 'error', $message);

        if ($recoveryCodes !== null) {
            session()->flash('recovery_codes', $recoveryCodes);
        }

        return redirect()->route('two-factor.settings');
    }
}
