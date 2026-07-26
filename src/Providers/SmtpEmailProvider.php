<?php

namespace Jmrashed\TwoFactorAuth\Providers;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Jmrashed\TwoFactorAuth\Contracts\EmailProvider;

class SmtpEmailProvider implements EmailProvider
{
    public function send(string $email, string $code): bool
    {
        try {
            Mail::raw("Your 2FA code is: {$code}", static function ($message) use ($email): void {
                $message->to($email)->subject('Two-Factor Authentication Code');
            });

            return true;
        } catch (\Throwable $e) {
            Log::error('Two-Factor email via SMTP threw an exception.', ['exception' => $e->getMessage()]);

            return false;
        }
    }
}
