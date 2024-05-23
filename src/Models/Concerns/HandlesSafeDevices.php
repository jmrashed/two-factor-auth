<?php

/**
 * @author [jmrashed]
 * @email [jmrashed@mail.com]
 * @create date 2024-05-23 13:59:42
 * @modify date 2024-05-23 13:59:42
 * @desc [ two-factor ]
 */
namespace Jmrashed\TwoFactorAuth\Models\Concerns;

use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

trait HandlesSafeDevices
{
    /**
     * Returns the timestamp of the Safe Device.
     */
    public function getSafeDeviceTimestamp(string $token = null): ?Carbon
    {
        if ($token && $device = $this->safe_devices?->firstWhere('2fa_remember', $token)) {
            return Carbon::createFromTimestamp($device['added_at']);
        }

        return null;
    }

    /**
     * Generates a Device token to bypass Two-Factor Authentication.
     */
    public static function generateDefaultTwoFactorRemember(): string
    {
        return Str::random(100);
    }
}
