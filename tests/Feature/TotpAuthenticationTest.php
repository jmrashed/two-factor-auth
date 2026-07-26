<?php

namespace Jmrashed\TwoFactorAuth\Tests\Feature;

use Jmrashed\TwoFactorAuth\Tests\Fixtures\User;
use Jmrashed\TwoFactorAuth\Tests\TestCase;

class TotpAuthenticationTest extends TestCase
{
    protected function makeUser(): User
    {
        return User::create([
            'name' => 'Alice',
            'email' => 'alice@example.com',
            'password' => 'secret',
        ]);
    }

    public function test_user_can_enable_and_confirm_totp(): void
    {
        $user = $this->makeUser();

        $this->assertFalse($user->hasTwoFactorEnabled());

        $user->createTwoFactorAuth();

        $code = $user->makeTwoFactorCode();

        $this->assertTrue($user->confirmTwoFactorAuth($code));
        $this->assertTrue($user->hasTwoFactorEnabled());
    }

    public function test_confirming_with_invalid_code_fails(): void
    {
        $user = $this->makeUser();
        $user->createTwoFactorAuth();

        $this->assertFalse($user->confirmTwoFactorAuth('000000'));
        $this->assertFalse($user->hasTwoFactorEnabled());
    }

    public function test_valid_totp_code_is_accepted_once(): void
    {
        $user = $this->makeUser();
        $user->createTwoFactorAuth();
        $code = $user->makeTwoFactorCode();
        $user->confirmTwoFactorAuth($code);

        // A code from the previous TOTP period (still inside the configured window,
        // and distinct from the one already burned during confirmation).
        $freshCode = $user->makeTwoFactorCode(now()->subSeconds(30));
        $this->assertTrue($user->validateTwoFactorCode($freshCode));

        // Replaying the very same code must be rejected (one-time use).
        $this->assertFalse($user->validateTwoFactorCode($freshCode));
    }

    public function test_disabling_two_factor_flushes_auth_data(): void
    {
        $user = $this->makeUser();
        $user->createTwoFactorAuth();
        $user->confirmTwoFactorAuth($user->makeTwoFactorCode());

        $user->disableTwoFactorAuth();

        $this->assertFalse($user->hasTwoFactorEnabled());
    }
}
