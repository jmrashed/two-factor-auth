<?php

namespace Jmrashed\TwoFactorAuth\Tests\Fixtures;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Jmrashed\TwoFactorAuth\Contracts\TwoFactorAuthenticatable;
use Jmrashed\TwoFactorAuth\TwoFactorAuthentication;

class User extends Authenticatable implements TwoFactorAuthenticatable
{
    use TwoFactorAuthentication;

    protected $table = 'users';

    protected $fillable = ['name', 'email', 'password'];

    protected $hidden = ['password'];
}
