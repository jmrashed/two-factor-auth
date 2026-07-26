<?php

namespace Jmrashed\TwoFactorAuth\Tests\Feature;

use Jmrashed\TwoFactorAuth\Tests\Fixtures\User;
use Jmrashed\TwoFactorAuth\Tests\TestCase;

class RecoveryCodesTest extends TestCase
{
    protected function makeEnabledUser(): User
    {
        $user = User::create([
            'name' => 'Bob',
            'email' => 'bob@example.com',
            'password' => 'secret',
        ]);

        $user->createTwoFactorAuth();
        $user->confirmTwoFactorAuth($user->makeTwoFactorCode());

        return $user;
    }

    public function test_recovery_codes_are_generated_on_enable(): void
    {
        $user = $this->makeEnabledUser();

        $this->assertCount(config('two-factor.recovery.codes'), $user->getRecoveryCodes());
    }

    public function test_a_recovery_code_can_be_used_once(): void
    {
        $user = $this->makeEnabledUser();
        $code = $user->getRecoveryCodes()->pluck('code')->first();

        $this->assertTrue($user->validateTwoFactorCode($code));
        $this->assertFalse($user->validateTwoFactorCode($code));
    }

    public function test_regenerating_recovery_codes_replaces_the_set(): void
    {
        $user = $this->makeEnabledUser();
        $originalFirstCode = $user->getRecoveryCodes()->pluck('code')->first();

        $fresh = $user->generateRecoveryCodes();

        $this->assertNotEquals($originalFirstCode, $fresh->pluck('code')->first());
        $this->assertFalse($user->validateTwoFactorCode($originalFirstCode));
    }
}
