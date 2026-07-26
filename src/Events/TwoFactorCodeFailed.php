<?php

namespace Jmrashed\TwoFactorAuth\Events;

use Illuminate\Queue\SerializesModels;
use Jmrashed\TwoFactorAuth\Contracts\TwoFactorAuthenticatable;

class TwoFactorCodeFailed
{
    use SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(public TwoFactorAuthenticatable $user, public string $method)
    {
        //
    }
}
