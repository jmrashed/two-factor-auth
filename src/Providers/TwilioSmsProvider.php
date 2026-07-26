<?php

namespace Jmrashed\TwoFactorAuth\Providers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Jmrashed\TwoFactorAuth\Contracts\SmsProvider;

use function config;

class TwilioSmsProvider implements SmsProvider
{
    public function send(string $phoneNumber, string $code): bool
    {
        $config = config('two-factor.sms.providers.twilio');

        if (empty($config['sid']) || empty($config['token']) || empty($config['from'])) {
            Log::warning('Two-Factor SMS not sent: Twilio provider is not fully configured.');

            return false;
        }

        try {
            $response = Http::withBasicAuth($config['sid'], $config['token'])
                ->asForm()
                ->post("https://api.twilio.com/2010-04-01/Accounts/{$config['sid']}/Messages.json", [
                    'From' => $config['from'],
                    'To' => $phoneNumber,
                    'Body' => "Your 2FA code is: {$code}",
                ]);

            if (! $response->successful()) {
                Log::warning('Two-Factor SMS via Twilio failed.', ['status' => $response->status(), 'body' => $response->body()]);
            }

            return $response->successful();
        } catch (\Throwable $e) {
            Log::error('Two-Factor SMS via Twilio threw an exception.', ['exception' => $e->getMessage()]);

            return false;
        }
    }
}
