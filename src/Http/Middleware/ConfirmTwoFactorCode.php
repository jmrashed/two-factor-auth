<?php
/**
 * @author [jmrashed]
 * @email [jmrashed@mail.com]
 * @create date 2024-05-23 13:59:42
 * @modify date 2024-05-23 13:59:42
 * @desc [ two-factor ]
 */

namespace Jmrashed\TwoFactorAuth\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Jmrashed\TwoFactorAuth\Contracts\TwoFactorAuthenticatable as TwoFactorAuth;

use function config;
use function now;
use function response;
use function url;

class ConfirmTwoFactorCode
{
    /**
     * Handle an incoming request.
     */
    public function handle(
        Request $request,
        Closure $next,
        string $route = '2fa.confirm',
        string|bool $force = 'false'
    ): mixed {
        $user = $request->user();

        if (
            ! $user instanceof TwoFactor ||
            ! $user->hasTwoFactorEnabled() ||
            $this->recentlyConfirmed($request, $route, $force)
        ) {
            return $next($request);
        }

        $route = $this->getRedirectionRoute($route);

        return $request->expectsJson()
            ? response()->json(['message' => trans('two-factor::messages.required')], 403)
            : response()->redirectGuest(url()->route($route));
    }

    /**
     * Determine the route to redirect the user.
     */
    protected function getRedirectionRoute(string $route): string
    {
        // If the developer is forcing this middleware to always run,
        // then return redirection route "2fa.confirm" as default.
        // Otherwise, return the route as the developer set it.
        if (in_array(strtolower($route), ['true', 'force'], true)) {
            return '2fa.confirm';
        }

        return $route;
    }

    /**
     * Determine if the confirmation timeout has expired.
     */
    protected function recentlyConfirmed(Request $request, string $route, string $force): bool
    {
        // If the developer is forcing this middleware to always run regardless of the
        // confirmation "reminder", then skip that logic and always return "false".
        // Otherwise, find the session key and check it has not expired already.
        if (
            in_array(strtolower($route), ['true', 'force'], true) ||
            in_array(strtolower($force), ['true', 'force'], true)
        ) {
            return false;
        }

        $key = config('two-factor.confirm.key');

        return $request->session()->get("$key.confirm.expires_at") >= now()->getTimestamp();
    }
}
