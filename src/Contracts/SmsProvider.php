<?php

namespace Jmrashed\TwoFactorAuth\Contracts;

interface SmsProvider
{
    /**
     * Send the given Two-Factor code to the phone number. Returns whether it was sent successfully.
     */
    public function send(string $phoneNumber, string $code): bool;
}
