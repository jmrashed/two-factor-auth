<?php

/**
 * @author [jmrashed]
 *
 * @email [jmrashed@mail.com]
 *
 * @create date 2024-05-23 13:59:42
 *
 * @modify date 2024-05-23 13:59:42
 *
 * @desc [ two-factor ]
 */

namespace Jmrashed\TwoFactorAuth\Contracts;

use DateTimeInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Jmrashed\TwoFactorAuth\Models\TwoFactorAuthentication;

/**
 * @property-read TwoFactorAuthentication $twoFactorAuth
 */
interface TwoFactorAuthenticatable
{
    /**
     * Determines if the User has Two-Factor Authentication enabled or not.
     */
    public function hasTwoFactorEnabled(): bool;

    /**
     * Enables Two-Factor Authentication for the given user.
     */
    public function enableTwoFactorAuth(): void;

    /**
     * Disables Two-Factor Authentication for the given user.
     */
    public function disableTwoFactorAuth(): void;

    /**
     * Recreates the Two-Factor Authentication from the ground up, and returns a new Shared Secret.
     */
    public function createTwoFactorAuth(): TwoFactorTotp;

    /**
     * Confirms the Shared Secret and fully enables the Two-Factor Authentication.
     */
    public function confirmTwoFactorAuth(string $code): bool;

    /**
     * Validates the TOTP Code or Recovery Code.
     */
    public function validateTwoFactorCode(?string $code = null, bool $useRecoveryCodes = true): bool;

    /**
     * Makes a Two-Factor Code for a given time, and period offset.
     */
    public function makeTwoFactorCode(DateTimeInterface|int|string $at = 'now', int $offset = 0): string;

    /**
     * Return the current set of Recovery Codes.
     */
    public function getRecoveryCodes(): Collection;

    /**
     * Generates a new set of Recovery Codes.
     */
    public function generateRecoveryCodes(): Collection;

    /**
     * Return all the Safe Devices that bypass Two-Factor Authentication.
     */
    public function safeDevices(): Collection;

    /**
     * Adds a "safe" Device from the Request, and returns the token used to identify it.
     */
    public function addSafeDevice(Request $request): string;

    /**
     * Determines if the Request has been made through a previously used "safe" device.
     */
    public function isSafeDevice(Request $request): bool;

    /**
     * Sets a flag on the User to signal that a recent Two-Factor challenge has been bypassed under a Safe Device.
     */
    public function setTwoFactorBypassedBySafeDevice(bool $condition): void;

    /**
     * Determines if a recent Two-Factor challenge has been bypassed under a Safe Device.
     */
    public function wasTwoFactorBypassedBySafeDevice(): bool;

    /**
     * Set the preferred 2FA method for the user.
     */
    public function setTwoFactorMethod(string $method): void;

    /**
     * Get the user's preferred 2FA method.
     */
    public function getTwoFactorMethod(): string;

    /**
     * Set and verify the user's phone number for SMS 2FA.
     */
    public function setPhoneNumber(string $phoneNumber): void;

    /**
     * Verify the user's phone number.
     */
    public function verifyPhoneNumber(): void;

    /**
     * Check if phone number is verified.
     */
    public function isPhoneNumberVerified(): bool;

    /**
     * Enable email-based 2FA.
     */
    public function enableEmailTwoFactor(): void;

    /**
     * Disable email-based 2FA.
     */
    public function disableEmailTwoFactor(): void;

    /**
     * Check if email 2FA is enabled.
     */
    public function isEmailTwoFactorEnabled(): bool;

    /**
     * Generate and send SMS 2FA code.
     */
    public function sendSmsCode(): bool;

    /**
     * Generate and send email 2FA code.
     */
    public function sendEmailCode(): bool;

    /**
     * Validate SMS or Email 2FA code.
     */
    public function validateAlternativeTwoFactorCode(string $code, string $method): bool;
}
