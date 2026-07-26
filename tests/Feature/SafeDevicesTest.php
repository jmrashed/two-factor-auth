<?php

namespace Jmrashed\TwoFactorAuth\Tests\Feature;

use Illuminate\Http\Request;
use Jmrashed\TwoFactorAuth\Tests\Fixtures\User;
use Jmrashed\TwoFactorAuth\Tests\TestCase;

class SafeDevicesTest extends TestCase
{
    protected function makeEnabledUser(): User
    {
        $user = User::create([
            'name' => 'Carol',
            'email' => 'carol@example.com',
            'password' => 'secret',
        ]);

        $user->createTwoFactorAuth();
        $user->confirmTwoFactorAuth($user->makeTwoFactorCode());

        return $user;
    }

    public function test_adding_a_safe_device_bypasses_future_challenges(): void
    {
        $user = $this->makeEnabledUser();
        $request = Request::create('/login', 'POST');

        $token = $user->addSafeDevice($request);

        $bypassRequest = Request::create('/login', 'POST');
        $bypassRequest->cookies->set(config('two-factor.safe_devices.cookie'), $token);

        $this->assertTrue($user->isSafeDevice($bypassRequest));
    }

    public function test_unknown_device_is_not_safe(): void
    {
        $user = $this->makeEnabledUser();
        $request = Request::create('/login', 'POST');

        $this->assertFalse($user->isSafeDevice($request));
    }

    public function test_flushing_safe_devices_removes_them_all(): void
    {
        $user = $this->makeEnabledUser();
        $token = $user->addSafeDevice(Request::create('/login', 'POST'));

        $user->flushSafeDevices();

        $checkRequest = Request::create('/login', 'POST');
        $checkRequest->cookies->set(config('two-factor.safe_devices.cookie'), $token);

        $this->assertFalse($user->isSafeDevice($checkRequest));
    }
}
