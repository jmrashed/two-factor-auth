# Documentation

This is the extended reference for the Laravel Two-Factor Authentication (2FA) package. For a quick start, see [README.md](README.md).

## Table of Contents

- [Configuration](#configuration)
- [Enabling 2FA on a model](#enabling-2fa-on-a-model)
- [Middleware](#middleware)
- [The `totp` validation rule](#the-totp-validation-rule)
- [Login flow with `Auth2FA`](#login-flow-with-auth2fa)
- [The confirmation challenge routes](#the-confirmation-challenge-routes)
- [SMS and Email providers](#sms-and-email-providers)
- [Queueing SMS/Email delivery](#queueing-smsemail-delivery)
- [Recovery codes](#recovery-codes)
- [Safe (trusted) devices](#safe-trusted-devices)
- [Events](#events)
- [JSON / API responses](#json--api-responses)
- [Testing](#testing)

## Configuration

After running `php artisan two-factor-auth:install` and `php artisan migrate`, the package configuration lives in `config/two-factor.php`. Key sections:

- `cache` — where one-time TOTP codes are marked as "used" to prevent replay.
- `recovery` — whether recovery codes are generated, how many, and how long each one is.
- `safe_devices` — "remember this device" cookie behaviour.
- `confirm` — how long a `2fa.confirm` challenge stays satisfied in the session.
- `totp` — digits, period and algorithm used for new TOTP secrets (RFC 6238).
- `sms` / `email` — alternative 2FA methods, their providers, and whether sending is queued.
- `recovery_regeneration_cooldown` — minutes a user must wait between recovery code regenerations.

## Enabling 2FA on a model

Add the `TwoFactorAuthenticatable` contract and `TwoFactorAuthentication` trait to any model (usually your `User`):

```php
use Jmrashed\TwoFactorAuth\TwoFactorAuthentication;
use Jmrashed\TwoFactorAuth\Contracts\TwoFactorAuthenticatable;

class User extends Authenticatable implements TwoFactorAuthenticatable
{
    use TwoFactorAuthentication;
}
```

This gives the model methods like `hasTwoFactorEnabled()`, `createTwoFactorAuth()`, `confirmTwoFactorAuth()`, `validateTwoFactorCode()`, `generateRecoveryCodes()`, `addSafeDevice()`, `sendSmsCode()`, `sendEmailCode()`, and more — see [`Contracts\TwoFactorAuthenticatable`](src/Contracts/TwoFactorAuthenticatable.php) for the full list.

## Middleware

Two middleware aliases are registered automatically:

- **`2fa.enabled`** (`RequireTwoFactorEnabled`) — blocks access to a route unless the user has 2FA enabled, redirecting to the `2fa.notice` route (or a route you name as the middleware's argument).

  ```php
  Route::middleware('2fa.enabled')->group(function () {
      // Routes only reachable once the user has 2FA turned on.
  });
  ```

- **`2fa.confirm`** (`ConfirmTwoFactorCode`) — asks an already-authenticated user to re-confirm with a 2FA code before accessing a sensitive route, remembering the confirmation in the session for `two-factor.confirm.time` minutes.

  ```php
  Route::middleware('2fa.confirm')->group(function () {
      // Sensitive routes: settings, billing, etc.
  });
  ```

  Pass `force` as the middleware argument (`2fa.confirm:force`) to always require a fresh confirmation, ignoring the session timeout.

## The `totp` validation rule

A `totp` validation rule is registered for use in form requests:

```php
$request->validate([
    'code' => ['required', 'totp'],
]);
```

It validates the code against the currently authenticated user's shared secret.

## Login flow with `Auth2FA`

Use the `Auth2FA` facade (backed by `TwoFactorLoginHelper`) in your login controller instead of Laravel's default `Auth::attempt()`:

```php
use Jmrashed\TwoFactorAuth\Facades\Auth2FA;

public function login(Request $request)
{
    if (Auth2FA::attempt($request->only('email', 'password'))) {
        return redirect()->intended();
    }

    return back()->withErrors(['email' => 'Invalid credentials.']);
}
```

If the user has 2FA enabled, a `InvalidCodeException` is caught internally and the credentials are safely flashed (encrypted) into the session so the 2FA code can be requested on the next request, without asking the user to type their password again.

## The confirmation challenge routes

For the *login-time* 2FA challenge (as opposed to the `2fa.confirm` middleware, which re-confirms an already-logged-in session), the package registers:

| Route | Name | Purpose |
|---|---|---|
| `GET /two-factor-confirm` | `2fa.confirm` | Renders the code entry form. |
| `POST /two-factor-confirm/send-code` | `2fa.confirm.send-code` | Sends/resends an SMS or Email code (JSON). |
| `POST /two-factor-confirm` | `2fa.confirm.store` | Verifies the submitted code. |
| `GET /two-factor-notice` | `2fa.notice` | Shown when `2fa.enabled` blocks a user who hasn't turned on 2FA yet. |

## SMS and Email providers

SMS and Email 2FA are implemented behind small provider interfaces so you can swap or add providers without touching the package internals:

- `Jmrashed\TwoFactorAuth\Contracts\SmsProvider` — `send(string $phoneNumber, string $code): bool`
- `Jmrashed\TwoFactorAuth\Contracts\EmailProvider` — `send(string $email, string $code): bool`

Built-in implementations: `Providers\TwilioSmsProvider`, `Providers\SmtpEmailProvider` (uses Laravel's configured mailer), `Providers\MailgunEmailProvider`.

To add your own, implement the interface and point `two-factor.sms.providers.custom.class` (or `email...providers.custom.class`) at your class:

```php
// config/two-factor.php
'sms' => [
    'default_provider' => 'custom',
    'providers' => [
        'custom' => ['class' => \App\Sms\VonageProvider::class],
    ],
],
```

## Queueing SMS/Email delivery

Set `two-factor.sms.queue` / `two-factor.email.queue` to `true` (or `TWO_FACTOR_SMS_QUEUE=true` / `TWO_FACTOR_EMAIL_QUEUE=true` in `.env`) to push delivery onto your configured queue connection instead of blocking the HTTP request. With the default `sync` queue driver this is a no-op — the job just runs inline.

## Recovery codes

Recovery codes are generated automatically when 2FA is enabled (if `two-factor.recovery.enabled` is `true`). Manage them with:

```php
$user->getRecoveryCodes();       // Current set (with 'used_at' markers)
$user->generateRecoveryCodes();  // Rotates the whole set
```

The bundled settings page lets a user regenerate their own codes, gated behind a current-password confirmation and the `recovery_regeneration_cooldown` throttle.

## Safe (trusted) devices

When `two-factor.safe_devices.enabled` is `true`, `addSafeDevice($request)` remembers the current device (via a cookie) for `expiration_days`, bypassing future 2FA challenges on that device:

```php
if ($user->isSafeDevice($request)) {
    // Skip the 2FA challenge.
} else {
    // ...verify code, then:
    $user->addSafeDevice($request);
}
```

## Events

| Event | Fired when |
|---|---|
| `TwoFactorEnabled` | 2FA is turned on. |
| `TwoFactorDisabled` | 2FA is turned off. |
| `TwoFactorRecoveryCodesGenerated` | A new recovery code batch is generated. |
| `TwoFactorRecoveryCodesDepleted` | The last unused recovery code is consumed. |
| `TwoFactorMethodChanged` | The active method (`totp`/`sms`/`email`) changes. |
| `TwoFactorCodeSent` | An SMS/Email code send was attempted (`$successful` tells you the outcome). |
| `TwoFactorCodeFailed` | An SMS/Email code validation attempt failed. |
| `TwoFactorSafeDeviceAdded` | A device is added to the safe/trusted list. |

## JSON / API responses

`TwoFactorSettingsController::updateSettings` returns JSON when the request `Accept`s JSON (e.g. `Accept: application/json`), so SPA/mobile clients can drive the same settings flow without a full page redirect:

```json
{ "message": "SMS Two-Factor Authentication has been enabled." }
```

## Testing

The package ships with its own PHPUnit + Orchestra Testbench suite (`tests/`). Run it with:

```bash
composer test        # PHPUnit with coverage
composer stan         # PHPStan
composer pint         # Laravel Pint (code style)
```
