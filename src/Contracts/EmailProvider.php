<?php

namespace Jmrashed\TwoFactorAuth\Contracts;

interface EmailProvider
{
    /**
     * Send the given Two-Factor code to the email address. Returns whether it was sent successfully.
     */
    public function send(string $email, string $code): bool;
}
