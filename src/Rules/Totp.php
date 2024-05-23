<?php

/**
 * @author [jmrashed]
 * @email [jmrashed@mail.com]
 * @create date 2024-05-23 13:59:42
 * @modify date 2024-05-23 13:59:42
 * @desc [ two-factor ]
 */
namespace Jmrashed\TwoFactorAuth\Rules;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Arr;
use Jmrashed\TwoFactorAuth\Contracts\TwoFactorAuthenticatable;

use function is_string;

/**
 * @internal
 */
class Totp
{
    /**
     * Create a new "totp code" rule instance.
     */
    public function __construct(protected ?Authenticatable $user = null)
    {
        //
    }

    /**
     * Validate that an attribute is a valid Two-Factor Authentication TOTP code.
     */
    public function validate(string $attribute, mixed $value, array $parameters): bool
    {
        return is_string($value)
            && $this->user instanceof TwoFactorAuthenticatable
            && $this->user->validateTwoFactorCode($value, Arr::first($parameters) !== 'code');
    }
}
