<?php

namespace Jmrashed\TwoFactorAuth\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Illuminate\Validation\Rule;
use Jmrashed\TwoFactorAuth\Contracts\TwoFactorAuthenticatable;
use Jmrashed\TwoFactorAuth\TwoFactor;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Module\MatrixFactory;
use BaconQrCode\Writer;
use function config;
use function redirect;
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
        /** @var TwoFactorAuthenticatable $user */
        $user = $request->user();

        $qrCodeSvg = null;
        if ($user->hasTwoFactorEnabled() && $user->getTwoFactorMethod() === 'totp') {
            $renderer = new ImageRenderer(
                new MatrixFactory(),
                new SvgImageBackEnd()
            );

            $writer = new Writer($renderer);

            $qrCodeSvg = $writer->writeString($user->twoFactorAuth->toQr());
        }

        return view('two-factor::settings', [
            'user' => $user,
            'qrCodeSvg' => $qrCodeSvg,
        ]);
    }

    /**
     * Update the 2FA settings.
     */
    public function updateSettings(Request $request): RedirectResponse
    {
        /** @var TwoFactorAuthenticatable $user */
        $user = $request->user();
        $action = $request->input('action');

        switch ($action) {
            case 'enable_totp':
                $user->createTwoFactorAuth();
                $user->enableTwoFactorAuth();
                session()->flash('status', trans('two-factor::messages.totp_enabled'));
                break;
            case 'confirm_totp':
                $request->validate([
                    'totp_code' => ['required', 'string', 'size:' . config('two-factor.totp.digits')],
                ]);
                if ($user->confirmTwoFactorAuth($request->input('totp_code'))) {
                    $user->setTwoFactorMethod('totp');
                    session()->flash('status', trans('two-factor::messages.totp_confirmed'));
                } else {
                    session()->flash('error', trans('two-factor::messages.invalid_totp_code'));
                }
                break;
            case 'disable_totp':
                $user->disableTwoFactorAuth();
                session()->flash('status', trans('two-factor::messages.totp_disabled'));
                break;
            case 'enable_sms':
                $request->validate([
                    'phone_number' => 'required|string|max:20',
                ]);
                $user->setPhoneNumber($request->input('phone_number'));
                $user->setTwoFactorMethod('sms');
                session()->flash('status', trans('two-factor::messages.sms_enabled'));
                break;
            case 'send_sms_code':
                $request->validate([
                    'phone_number' => 'required|string|max:20',
                ]);
                $user->setPhoneNumber($request->input('phone_number'));
                if ($user->sendSmsCode()) {
                    session()->flash('status', trans('two-factor::messages.sms_code_sent'));
                } else {
                    session()->flash('error', trans('two-factor::messages.sms_send_failed'));
                }
                break;
            case 'verify_sms':
                $request->validate([
                    'sms_code' => 'required|string|size:' . config('two-factor.sms.code_length', 6),
                ]);
                if ($user->validateAlternativeTwoFactorCode($request->input('sms_code'), 'sms')) {
                    $user->verifyPhoneNumber();
                    session()->flash('status', trans('two-factor::messages.sms_verified'));
                } else {
                    session()->flash('error', trans('two-factor::messages.invalid_sms_code'));
                }
                break;
            case 'disable_sms':
                $user->setPhoneNumber(null); // Clear phone number
                $user->setTwoFactorMethod('totp'); // Revert to TOTP or no 2FA
                session()->flash('status', trans('two-factor::messages.sms_disabled'));
                break;
            case 'enable_email':
                $user->enableEmailTwoFactor();
                $user->setTwoFactorMethod('email');
                session()->flash('status', trans('two-factor::messages.email_enabled'));
                break;
            case 'send_email_code':
                if ($user->sendEmailCode()) {
                    session()->flash('status', trans('two-factor::messages.email_code_sent'));
                } else {
                    session()->flash('error', trans('two-factor::messages.email_send_failed'));
                }
                break;
            case 'verify_email':
                $request->validate([
                    'email_code' => 'required|string|size:' . config('two-factor.email.code_length', 6),
                ]);
                if ($user->validateAlternativeTwoFactorCode($request->input('email_code'), 'email')) {
                    session()->flash('status', trans('two-factor::messages.email_verified'));
                } else {
                    session()->flash('error', trans('two-factor::messages.invalid_email_code'));
                }
                break;
            case 'disable_email':
                $user->disableEmailTwoFactor();
                $user->setTwoFactorMethod('totp'); // Revert to TOTP or no 2FA
                session()->flash('status', trans('two-factor::messages.email_disabled'));
                break;
            default:
                session()->flash('error', trans('two-factor::messages.invalid_action'));
                break;
        }

        return redirect()->route('two-factor.settings');
    }
}