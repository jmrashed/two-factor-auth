<?php
/**
 * @author [jmrashed]
 * @email [jmrashed@mail.com]
 * @create date 2024-05-23 13:59:42
 * @modify date 2024-05-23 13:59:42
 * @desc [ two-factor ]
 */

namespace Jmrashed\TwoFactorAuth\Facades;

use Illuminate\Support\Facades\Facade;
use Jmrashed\TwoFactorAuth\TwoFactorLoginHelper;

/**
 * @method static bool attempt(array $credentials = [], mixed $remember = false)
 * @method static bool attemptWhen(array $credentials = [], array|callable|null  $callbacks = null, mixed $remember = false)
 * @method static \Jmrashed\TwoFactorAuth\TwoFactorLoginHelper view(string $view)
 * @method static \Jmrashed\TwoFactorAuth\TwoFactorLoginHelper message(string $message)
 * @method static \Jmrashed\TwoFactorAuth\TwoFactorLoginHelper input(string $input)
 * @method static \Jmrashed\TwoFactorAuth\TwoFactorLoginHelper sessionKey(string $sessionKey)
 * @method static \Jmrashed\TwoFactorAuth\TwoFactorLoginHelper guard(string $guard)
 * @method static \Jmrashed\TwoFactorAuth\TwoFactorLoginHelper redirect(string $route)
 *
 * @see \Jmrashed\TwoFactorAuth\TwoFactorLoginHelper
 */
class Auth2FA extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return TwoFactorLoginHelper::class;
    }
}
