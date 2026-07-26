<?php

namespace Jmrashed\TwoFactorAuth\Tests\Feature;

use Illuminate\Support\Facades\Event;
use Jmrashed\TwoFactorAuth\Contracts\EmailProvider;
use Jmrashed\TwoFactorAuth\Contracts\SmsProvider;
use Jmrashed\TwoFactorAuth\Events\TwoFactorCodeFailed;
use Jmrashed\TwoFactorAuth\Events\TwoFactorCodeSent;
use Jmrashed\TwoFactorAuth\Tests\Fixtures\User;
use Jmrashed\TwoFactorAuth\Tests\TestCase;

class AlternativeTwoFactorMethodsTest extends TestCase
{
    protected function makeUser(): User
    {
        return User::create(['name' => 'Heidi', 'email' => 'heidi@example.com', 'password' => 'secret']);
    }

    public function test_sms_code_is_sent_and_validated_through_the_configured_provider(): void
    {
        Event::fake();

        config(['two-factor.sms.enabled' => true]);

        $fake = new class implements SmsProvider
        {
            public array $sent = [];

            public function send(string $phoneNumber, string $code): bool
            {
                $this->sent[] = [$phoneNumber, $code];

                return true;
            }
        };
        $this->app->instance(get_class($fake), $fake);
        config(['two-factor.sms.providers.twilio.class' => get_class($fake)]);

        $user = $this->makeUser();
        $user->setPhoneNumber('+15551234567');

        $this->assertTrue($user->sendSmsCode());
        $this->assertCount(1, $fake->sent);
        [$phone, $code] = $fake->sent[0];
        $this->assertSame('+15551234567', $phone);

        $this->assertTrue($user->validateAlternativeTwoFactorCode($code, 'sms'));
        // Codes are one-time use.
        $this->assertFalse($user->validateAlternativeTwoFactorCode($code, 'sms'));

        Event::assertDispatched(TwoFactorCodeSent::class, fn ($event) => $event->method === 'sms' && $event->successful);
        Event::assertDispatched(TwoFactorCodeFailed::class, fn ($event) => $event->method === 'sms');
    }

    public function test_email_code_is_sent_and_validated_through_the_configured_provider(): void
    {
        config(['two-factor.email.enabled' => true]);

        $fake = new class implements EmailProvider
        {
            public array $sent = [];

            public function send(string $email, string $code): bool
            {
                $this->sent[] = [$email, $code];

                return true;
            }
        };
        $this->app->instance(get_class($fake), $fake);
        config(['two-factor.email.providers.smtp.class' => get_class($fake)]);

        $user = $this->makeUser();
        $user->enableEmailTwoFactor();

        $this->assertTrue($user->sendEmailCode());
        $this->assertCount(1, $fake->sent);
        [, $code] = $fake->sent[0];

        $this->assertTrue($user->validateAlternativeTwoFactorCode($code, 'email'));
        $this->assertFalse($user->validateAlternativeTwoFactorCode($code, 'email'));
    }

    public function test_sms_is_not_sent_when_provider_is_unresolvable(): void
    {
        config(['two-factor.sms.enabled' => true]);
        config(['two-factor.sms.providers.twilio.class' => null]);

        $user = $this->makeUser();
        $user->setPhoneNumber('+15551234567');

        $this->assertFalse($user->sendSmsCode());
    }
}
