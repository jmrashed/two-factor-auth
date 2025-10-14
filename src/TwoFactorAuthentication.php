<?php

/**
 * @author [jmrashed]
 * @email [jmrashed@mail.com]
 * @create date 2024-05-23 13:59:42
 * @modify date 2024-05-23 13:59:42
 * @desc [ two-factor ]
 */
namespace Jmrashed\TwoFactorAuth;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use InvalidArgumentException;

use function collect;
use function config;
use function cookie;
use function event;
use function now;
use function rand;
use function app;

/**
 * @property-read \Jmrashed\TwoFactorAuth\Models\TwoFactorAuthentication $twoFactorAuth
 */
trait TwoFactorAuthentication
{
    /**
     * Determines if the last Two-Factor confirmation was bypassed under a Safe Device.
     *
     * @var bool
     */
    public bool $wasTwoFactorRecentlyBypassedUnderSafeDevice = false;

    /**
     * The currently active two-factor method.
     *
     * @var string|null
     */
    public ?string $twoFactorMethod = null;

    /**
     * Initialize the current Trait.
     */
    public function initializeTwoFactorAuthentication(): void
    {
        // For security, we will hide the Two-Factor Authentication data from the parent model.
        $this->makeHidden('twoFactorAuth');
    }

    /**
     * This connects the current Model to the Two-Factor Authentication model.
     */
    public function twoFactorAuth(): MorphOne
    {
        return $this->morphOne(Models\TwoFactorAuthentication::class, 'authenticatable')
            ->withDefault(static function (Models\TwoFactorAuthentication $model): Models\TwoFactorAuthentication {
                return $model->fill(config('two-factor.totp'));
            });
    }

    /**
     * Determines if the User has Two-Factor Authentication enabled.
     */
    public function hasTwoFactorEnabled(): bool
    {
        return $this->twoFactorAuth->isEnabled();
    }

    /**
     * Enables Two-Factor Authentication for the given user.
     */
    public function enableTwoFactorAuth(): void
    {
        $this->twoFactorAuth->enabled_at = now();

        if (config('two-factor.recovery.enabled')) {
            $this->generateRecoveryCodes();
        }

        $this->twoFactorAuth->save();

        event(new Events\TwoFactorEnabled($this));
    }

    /**
     * Disables Two-Factor Authentication for the given user.
     */
    public function disableTwoFactorAuth(): void
    {
        $this->twoFactorAuth->flushAuth()->delete();

        event(new Events\TwoFactorDisabled($this));
    }

    /**
     * Creates a new Two-Factor Auth mechanisms from scratch, and returns a new Shared Secret.
     */
    public function createTwoFactorAuth(): Contracts\TwoFactorTotp
    {
        $this->twoFactorAuth->flushAuth()->forceFill([
            'label' => $this->twoFactorLabel(),
        ])->save();

        return $this->twoFactorAuth;
    }

    /**
     * Returns the issuer name to use as part of the Two-Factor credential label.
     */
    public function getTwoFactorIssuer(): string
    {
        // Use the OTP issuer config, or back down to the application name.
        return config('two-factor.issuer') ?: config('app.name');
    }

    /**
     * Returns the user identifier to use as part of the Two-Factor credential label.
     */
    public function getTwoFactorUserIdentifier(): string
    {
        return $this->getAttribute('email');
    }

    /**
     * Returns the label for TOTP URI.
     */
    protected function twoFactorLabel(): string
    {
        if (! $issuer = $this->getTwoFactorIssuer()) {
            throw new InvalidArgumentException('The TOTP issuer cannot be empty.');
        }

        if (! $identifier = $this->getTwoFactorUserIdentifier()) {
            throw new InvalidArgumentException('The TOTP User Identifier cannot be empty.');
        }

        return $issuer.':'.$identifier;
    }

    /**
     * Confirms the Shared Secret and fully enables the Two-Factor Authentication.
     */
    public function confirmTwoFactorAuth(string $code): bool
    {
        // If the Two-Factor is already enabled, there is no need to re-confirm the code.
        if ($this->hasTwoFactorEnabled()) {
            return true;
        }

        if ($this->validateCode($code)) {
            $this->enableTwoFactorAuth();

            return true;
        }

        return false;
    }

    /**
     * Verifies the Code against the Shared Secret.
     */
    protected function validateCode(string|int $code): bool
    {
        return $this->twoFactorAuth->validateCode($code);
    }

    /**
     * Validates the TOTP Code or Recovery Code.
     */
    public function validateTwoFactorCode(?string $code = null, bool $useRecoveryCodes = true): bool
    {
        return null !== $code
            && $this->hasTwoFactorEnabled()
            && ($this->validateCode($code) || ($useRecoveryCodes && $this->useRecoveryCode($code)));
    }

    /**
     * Makes a Two-Factor Code for a given time, and period offset.
     */
    public function makeTwoFactorCode(DateTimeInterface|int|string $at = 'now', int $offset = 0): string
    {
        return $this->twoFactorAuth->makeCode($at, $offset);
    }

    /**
     * Determines if the User has Recovery Codes available.
     */
    protected function hasRecoveryCodes(): bool
    {
        return $this->twoFactorAuth->containsUnusedRecoveryCodes();
    }

    /**
     * Return the current set of Recovery Codes.
     */
    public function getRecoveryCodes(): Collection
    {
        return $this->twoFactorAuth->recovery_codes ?? collect();
    }

    /**
     * Generates a new set of Recovery Codes.
     */
    public function generateRecoveryCodes(): Collection
    {
        [
            'two-factor.recovery.codes' => $amount,
            'two-factor.recovery.length' => $length
        ] = config()->get([
            'two-factor.recovery.codes', 'two-factor.recovery.length',
        ]);

        $this->twoFactorAuth->recovery_codes = Models\TwoFactorAuthentication::generateRecoveryCodes($amount, $length);
        $this->twoFactorAuth->recovery_codes_generated_at = now();
        $this->twoFactorAuth->save();

        event(new Events\TwoFactorRecoveryCodesGenerated($this));

        return $this->twoFactorAuth->recovery_codes;
    }

    /**
     * Uses a one-time Recovery Code if there is one available.
     */
    protected function useRecoveryCode(string $code): bool
    {
        if (! $this->twoFactorAuth->setRecoveryCodeAsUsed($code)) {
            return false;
        }

        $this->twoFactorAuth->save();

        if (! $this->hasRecoveryCodes()) {
            event(new Events\TwoFactorRecoveryCodesDepleted($this));
        }

        return true;
    }

    /**
     * Adds a "safe" Device from the Request, and returns the token used.
     */
    public function addSafeDevice(Request $request): string
    {
        [$name, $expiration] = array_values(config()->get([
            'two-factor.safe_devices.cookie', 'two-factor.safe_devices.expiration_days',
        ]));

        $this->twoFactorAuth->safe_devices = $this->safeDevices()
            ->push([
                '2fa_remember' => $token = $this->generateTwoFactorRemember(),
                'ip' => $request->ip(),
                'added_at' => $this->freshTimestamp()->getTimestamp(),
            ])
            ->sortByDesc('added_at') // Ensure the last is the first, so we can slice it.
            ->slice(0, config('two-factor.safe_devices.max_devices', 3))
            ->values();

        $this->twoFactorAuth->save();

        cookie()->queue($name, $token, $expiration * 1440);

        return $token;
    }

    /**
     * Generates a Device token to bypass Two-Factor Authentication.
     */
    protected function generateTwoFactorRemember(): string
    {
        return Models\TwoFactorAuthentication::generateDefaultTwoFactorRemember();
    }

    /**
     * Deletes all saved safe devices.
     */
    public function flushSafeDevices(): bool
    {
        return $this->twoFactorAuth->setAttribute('safe_devices', null)->save();
    }

    /**
     * Return all the Safe Devices that bypass Two-Factor Authentication.
     */
    public function safeDevices(): Collection
    {
        return $this->twoFactorAuth->safe_devices ?? collect();
    }

    /**
     * Determines if the Request has been made through a previously used "safe" device.
     */
    public function isSafeDevice(Request $request): bool
    {
        $timestamp = $this->twoFactorAuth->getSafeDeviceTimestamp($this->getTwoFactorRememberFromRequest($request));

        if ($timestamp) {
            return $timestamp->addDays(config('two-factor.safe_devices.expiration_days'))->isFuture();
        }

        return false;
    }

    /**
     * Returns the Two-Factor Remember Token of the request.
     */
    protected function getTwoFactorRememberFromRequest(Request $request): ?string
    {
        return $request->cookie(config('two-factor.safe_devices.cookie', '2fa_remember'));
    }

    /**
     * Determines if the Request has been made through a not-previously-known device.
     */
    public function isNotSafeDevice(Request $request): bool
    {
        return ! $this->isSafeDevice($request);
    }

    /**
     * Sets a flag on the User to signal that a recent Two-Factor challenge has been bypassed under a Safe Device.
     */
    public function setTwoFactorBypassedBySafeDevice(bool $condition): void
    {
        $this->wasTwoFactorRecentlyBypassedUnderSafeDevice = $condition;
    }

    /**
     * Determines if a recent Two-Factor challenge has been bypassed under a Safe Device.
     */
    public function wasTwoFactorBypassedBySafeDevice(): bool
    {
        return $this->wasTwoFactorRecentlyBypassedUnderSafeDevice;
    }

    /**
     * Set the preferred 2FA method for the user.
     */
    public function setTwoFactorMethod(string $method): void
    {
        $this->twoFactorAuth->method = $method;
        $this->twoFactorAuth->save();
    }

    /**
     * Get the user's preferred 2FA method.
     */
    public function getTwoFactorMethod(): string
    {
        return $this->twoFactorAuth->method ?? 'totp';
    }

    /**
     * Set and verify the user's phone number for SMS 2FA.
     */
    public function setPhoneNumber(string $phoneNumber): void
    {
        $this->twoFactorAuth->phone_number = $phoneNumber;
        $this->twoFactorAuth->phone_number_verified_at = null;
        $this->twoFactorAuth->save();
    }

    /**
     * Verify the user's phone number.
     */
    public function verifyPhoneNumber(): void
    {
        $this->twoFactorAuth->phone_number_verified_at = now();
        $this->twoFactorAuth->save();
    }

    /**
     * Check if phone number is verified.
     */
    public function isPhoneNumberVerified(): bool
    {
        return $this->twoFactorAuth->phone_number_verified_at !== null;
    }

    /**
     * Enable email-based 2FA.
     */
    public function enableEmailTwoFactor(): void
    {
        $this->twoFactorAuth->email_2fa_enabled = true;
        $this->twoFactorAuth->save();
    }

    /**
     * Disable email-based 2FA.
     */
    public function disableEmailTwoFactor(): void
    {
        $this->twoFactorAuth->email_2fa_enabled = false;
        $this->twoFactorAuth->email_2fa_code = null;
        $this->twoFactorAuth->save();
    }

    /**
     * Check if email 2FA is enabled.
     */
    public function isEmailTwoFactorEnabled(): bool
    {
        return $this->twoFactorAuth->email_2fa_enabled ?? false;
    }

    /**
     * Generate and send SMS 2FA code.
     */
    public function sendSmsCode(): bool
    {
        if (!config('two-factor.sms.enabled') || !$this->twoFactorAuth->phone_number) {
            return false;
        }

        $code = $this->generateTwoFactorCode('sms');
        $cacheKey = "2fa_sms_{$this->getKey()}";
        
        Cache::put($cacheKey, $code, now()->addMinutes(config('two-factor.sms.code_lifetime', 10)));

        return $this->sendSmsViaProvider($this->twoFactorAuth->phone_number, $code);
    }

    /**
     * Generate and send email 2FA code.
     */
    public function sendEmailCode(): bool
    {
        if (!config('two-factor.email.enabled') || !$this->isEmailTwoFactorEnabled()) {
            return false;
        }

        $code = $this->generateTwoFactorCode('email');
        $this->twoFactorAuth->email_2fa_code = $code;
        $this->twoFactorAuth->save();

        return $this->sendEmailViaProvider($this->getTwoFactorUserIdentifier(), $code);
    }

    /**
     * Validate SMS or Email 2FA code.
     */
    public function validateAlternativeTwoFactorCode(string $code, string $method): bool
    {
        if ($method === 'sms') {
            return $this->validateSmsCode($code);
        }

        if ($method === 'email') {
            return $this->validateEmailCode($code);
        }

        return false;
    }

    /**
     * Generate a 2FA code for SMS or Email.
     */
    protected function generateTwoFactorCode(string $method): string
    {
        $length = $method === 'sms' 
            ? config('two-factor.sms.code_length', 6)
            : config('two-factor.email.code_length', 6);

        return str_pad(rand(0, pow(10, $length) - 1), $length, '0', STR_PAD_LEFT);
    }

    /**
     * Validate SMS code.
     */
    protected function validateSmsCode(string $code): bool
    {
        $cacheKey = "2fa_sms_{$this->getKey()}";
        $cachedCode = Cache::get($cacheKey);

        if ($cachedCode && $cachedCode === $code) {
            Cache::forget($cacheKey);
            return true;
        }

        return false;
    }

    /**
     * Validate email code.
     */
    protected function validateEmailCode(string $code): bool
    {
        if ($this->twoFactorAuth->email_2fa_code === $code) {
            $this->twoFactorAuth->email_2fa_code = null;
            $this->twoFactorAuth->save();
            return true;
        }

        return false;
    }

    /**
     * Send SMS via configured provider.
     */
    protected function sendSmsViaProvider(string $phoneNumber, string $code): bool
    {
        $provider = config('two-factor.sms.default_provider');

        if ($provider === 'twilio') {
            return $this->sendSmsViaTwilio($phoneNumber, $code);
        }

        return false;
    }

    /**
     * Send SMS via Twilio.
     */
    protected function sendSmsViaTwilio(string $phoneNumber, string $code): bool
    {
        $config = config('two-factor.sms.providers.twilio');
        
        if (!$config['sid'] || !$config['token'] || !$config['from']) {
            return false;
        }

        try {
            $response = Http::withBasicAuth($config['sid'], $config['token'])
                ->asForm()
                ->post("https://api.twilio.com/2010-04-01/Accounts/{$config['sid']}/Messages.json", [
                    'From' => $config['from'],
                    'To' => $phoneNumber,
                    'Body' => "Your 2FA code is: {$code}"
                ]);

            return $response->successful();
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Send email via configured provider.
     */
    protected function sendEmailViaProvider(string $email, string $code): bool
    {
        $provider = config('two-factor.email.default_provider');

        if ($provider === 'smtp') {
            return $this->sendEmailViaSmtp($email, $code);
        }

        if ($provider === 'mailgun') {
            return $this->sendEmailViaMailgun($email, $code);
        }

        return false;
    }

    /**
     * Send email via SMTP (Laravel Mail).
     */
    protected function sendEmailViaSmtp(string $email, string $code): bool
    {
        try {
            Mail::raw("Your 2FA code is: {$code}", function ($message) use ($email) {
                $message->to($email)
                    ->subject('Two-Factor Authentication Code');
            });
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Send email via Mailgun.
     */
    protected function sendEmailViaMailgun(string $email, string $code): bool
    {
        $config = config('two-factor.email.providers.mailgun');
        
        if (!$config['domain'] || !$config['secret']) {
            return false;
        }

        try {
            $response = Http::withBasicAuth('api', $config['secret'])
                ->asForm()
                ->post("https://api.mailgun.net/v3/{$config['domain']}/messages", [
                    'from' => "noreply@{$config['domain']}",
                    'to' => $email,
                    'subject' => 'Two-Factor Authentication Code',
                    'text' => "Your 2FA code is: {$code}"
                ]);

            return $response->successful();
        } catch (\Exception $e) {
            return false;
        }
    }
}
