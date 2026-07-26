<?php

namespace Jmrashed\TwoFactorAuth\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Jmrashed\TwoFactorAuth\Contracts\SmsProvider;

class SendTwoFactorSmsCode implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        public string $providerClass,
        public string $phoneNumber,
        public string $code,
    ) {
        //
    }

    public function handle(): void
    {
        /** @var SmsProvider $provider */
        $provider = app($this->providerClass);

        if (! $provider->send($this->phoneNumber, $this->code)) {
            Log::warning('Queued Two-Factor SMS code failed to send.', ['provider' => $this->providerClass]);
        }
    }
}
