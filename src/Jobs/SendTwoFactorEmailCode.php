<?php

namespace Jmrashed\TwoFactorAuth\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Jmrashed\TwoFactorAuth\Contracts\EmailProvider;

class SendTwoFactorEmailCode implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        public string $providerClass,
        public string $email,
        public string $code,
    ) {
        //
    }

    public function handle(): void
    {
        /** @var EmailProvider $provider */
        $provider = app($this->providerClass);

        if (! $provider->send($this->email, $this->code)) {
            Log::warning('Queued Two-Factor email code failed to send.', ['provider' => $this->providerClass]);
        }
    }
}
