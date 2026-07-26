<?php

namespace Jmrashed\TwoFactorAuth\Tests\Unit;

use Jmrashed\TwoFactorAuth\Facades\Auth2FA;
use Jmrashed\TwoFactorAuth\Tests\TestCase;
use Jmrashed\TwoFactorAuth\TwoFactorLoginHelper;

class PackageLoadTest extends TestCase
{
    public function test_package_service_provider_registers_routes(): void
    {
        $this->assertTrue($this->app['router']->has('two-factor.settings'));
        $this->assertTrue($this->app['router']->has('two-factor.settings.update'));
        $this->assertTrue($this->app['router']->has('2fa.confirm'));
        $this->assertTrue($this->app['router']->has('2fa.notice'));
    }

    public function test_facade_resolves_to_login_helper(): void
    {
        $this->assertInstanceOf(TwoFactorLoginHelper::class, Auth2FA::getFacadeRoot());
    }

    public function test_config_is_merged(): void
    {
        $this->assertIsArray(config('two-factor'));
        $this->assertSame(6, config('two-factor.totp.digits'));
    }
}
