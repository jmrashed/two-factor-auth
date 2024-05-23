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

use function response;
use function trans;

class RequireTwoFactorEnabled
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $route = '2fa.notice'): mixed
    {
        $user = $request->user();

        if (! $user instanceof TwoFactor || $user->hasTwoFactorEnabled()) {
            return $next($request);
        }

        return $request->expectsJson()
            ? response()->json(['message' => trans('two-factor::messages.enable')], 403)
            : response()->redirectToRoute($route);
    }
}
