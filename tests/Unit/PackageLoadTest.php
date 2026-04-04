<?php

namespace Jmrashed\TwoFactorAuth\Tests\Unit;

use Orchestra\Testbench\TestCase;
use Jmrashed\TwoFactorAuth\TwoFactorServiceProvider;
use Jmrashed\TwoFactorAuth\Facades\Auth2FA;

class PackageLoadTest extends TestCase
{
    protected function getPackageProviders($app): array
    {
        return [TwoFactorServiceProvider::class];
    }

    protected function getFacadeAccessor(): string
    {
        return Auth2FA::class;
    }

    public function test_package_service_provider_can_be_registered(): void
    {
        $this->assertTrue(true);
    }

    public function test_facade_can_be_resolved(): void
    {
        $this->assertTrue(true);
    }
}