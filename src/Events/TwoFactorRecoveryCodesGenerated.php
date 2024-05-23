<?php
/**
 * @author [jmrashed]
 * @email [jmrashed@mail.com]
 * @create date 2024-05-23 13:59:42
 * @modify date 2024-05-23 13:59:42
 * @desc [ two-factor ]
 */

namespace Jmrashed\TwoFactorAuth\Events;

use Illuminate\Queue\SerializesModels;
use Jmrashed\TwoFactorAuth\Contracts\TwoFactorAuthenticatable;

class TwoFactorRecoveryCodesGenerated
{
    use SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(public TwoFactorAuthenticatable $user)
    {
        //
    }
}
