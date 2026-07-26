<?php

/**
 * @author [jmrashed]
 *
 * @email [jmrashed@mail.com]
 *
 * @create date 2024-05-23 13:59:42
 *
 * @modify date 2024-05-23 13:59:42
 *
 * @desc [ two-factor ]
 */

namespace Jmrashed\TwoFactorAuth\Http\Controllers;

use const INF;

use Illuminate\Contracts\Config\Repository as ConfigContract;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;

use function now;
use function response;
use function trans;

class ConfirmTwoFactorCodeController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('throttle:60,1')->only('confirm');
    }

    /**
     * Display the 2FA code confirmation view.
     */
    public function form(Request $request): Response
    {
        $user = $request->user();
        $method = $user->getTwoFactorMethod();

        return response()->view('two-factor::confirm', compact('user', 'method'));
    }

    /**
     * Send 2FA code via current method.
     */
    public function sendCode(Request $request): JsonResponse
    {
        $user = $request->user();
        $method = $user->getTwoFactorMethod();

        $sent = match ($method) {
            'sms' => $user->sendSmsCode(),
            'email' => $user->sendEmailCode(),
            default => false,
        };

        if ($sent) {
            return response()->json(['message' => '2FA code sent successfully']);
        }

        return response()->json(['error' => 'Failed to send 2FA code'], 500);
    }

    /**
     * Confirm the given user's 2FA code (TOTP, SMS, or Email).
     */
    public function confirm(Request $request, ConfigContract $config): RedirectResponse|JsonResponse
    {
        $user = $request->user();
        $method = $user->getTwoFactorMethod();

        $request->validate([
            '2fa_code' => 'required|string',
            'method' => 'sometimes|string|in:totp,sms,email',
        ]);

        $code = $request->input('2fa_code');
        $requestedMethod = $request->input('method', $method);

        $isValid = match ($requestedMethod) {
            'totp' => $user->validateTwoFactorCode($code),
            'sms', 'email' => $user->validateAlternativeTwoFactorCode($code, $requestedMethod),
            default => false,
        };

        if (! $isValid) {
            return $request->wantsJson()
                ? response()->json(['error' => 'Invalid 2FA code'], 422)
                : back()->withErrors(['2fa_code' => 'Invalid 2FA code']);
        }

        $this->extendTotpConfirmationTimeout($request, $config);

        return $request->wantsJson()
            ? response()->json(['message' => trans('two-factor::messages.success')])
            : response()->redirectToIntended();
    }

    /**
     * Reset the TOTP code timeout.
     */
    protected function extendTotpConfirmationTimeout(Request $request, ConfigContract $config): void
    {
        [
            'two-factor.confirm.key' => $key,
            'two-factor.confirm.time' => $time
        ] = $config->get([
            'two-factor.confirm.key',
            'two-factor.confirm.time',
        ]);

        // This will let the developer remember the confirmation indefinitely.
        if ($time !== INF) {
            $time = now()->addMinutes($time)->getTimestamp();
        }

        $request->session()->put("$key.confirm.expires_at", $time);
    }
}
