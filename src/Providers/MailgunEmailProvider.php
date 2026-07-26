<?php

namespace Jmrashed\TwoFactorAuth\Providers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Jmrashed\TwoFactorAuth\Contracts\EmailProvider;

use function config;

class MailgunEmailProvider implements EmailProvider
{
    public function send(string $email, string $code): bool
    {
        $config = config('two-factor.email.providers.mailgun');

        if (empty($config['domain']) || empty($config['secret'])) {
            Log::warning('Two-Factor email not sent: Mailgun provider is not fully configured.');

            return false;
        }

        try {
            $response = Http::withBasicAuth('api', $config['secret'])
                ->asForm()
                ->post("https://api.mailgun.net/v3/{$config['domain']}/messages", [
                    'from' => "noreply@{$config['domain']}",
                    'to' => $email,
                    'subject' => 'Two-Factor Authentication Code',
                    'text' => "Your 2FA code is: {$code}",
                ]);

            if (! $response->successful()) {
                Log::warning('Two-Factor email via Mailgun failed.', ['status' => $response->status(), 'body' => $response->body()]);
            }

            return $response->successful();
        } catch (\Throwable $e) {
            Log::error('Two-Factor email via Mailgun threw an exception.', ['exception' => $e->getMessage()]);

            return false;
        }
    }
}
