# Changelog

## [1.1.1] - 2026-07-26

### Fixed
- **Critical**: `2fa.enabled`/`2fa.confirm` middleware checked `instanceof` against an undefined class, so 2FA enforcement silently never triggered
- **Critical**: Login-challenge routes (`ConfirmTwoFactorCodeController`) were never registered, making that flow unreachable
- **Critical**: Settings page QR code rendering used a nonexistent BaconQrCode class, fatally erroring for any TOTP-enabled user
- **Critical**: Enabling SMS/Email 2FA before TOTP caused a NOT NULL DB constraint violation
- `disable_sms` passed `null` to a non-nullable `setPhoneNumber(string $phoneNumber)`, causing a `TypeError`
- Removed a duplicate migration that collided with an existing one on `php artisan migrate`
- CI lock file resolved PHP 8.2+-only dependencies despite the package declaring PHP 8.1+ support

### Security
- Email 2FA codes moved from indefinite plaintext DB storage to expiring, cache-backed storage (matching SMS)
- Added password re-confirmation before disabling 2FA or regenerating recovery codes
- Added rate limiting to the 2FA settings update route
- Added E.164 phone number validation for SMS 2FA
- SMS/Email send failures are now logged instead of silently swallowed

### Added
- Real `SmsProvider`/`EmailProvider` interfaces with Twilio/SMTP/Mailgun implementations and a working `custom` provider slot
- Optional queueing of SMS/Email delivery (`two-factor.sms.queue` / `two-factor.email.queue`)
- New events: `TwoFactorCodeSent`, `TwoFactorCodeFailed`, `TwoFactorSafeDeviceAdded`, `TwoFactorMethodChanged`
- JSON/API responses from `TwoFactorSettingsController` for SPA/mobile consumers
- Recovery code regeneration cooldown
- Spanish (`es`) translation
- Real test suite (20 tests), PHPStan and Laravel Pint wired into CI

### Changed
- `docs.md` and `MIGRATIONS.md` corrected (previously empty / referencing a nonexistent `Models\User` class)
- README corrected to describe the middleware the package actually ships

## [1.1.0] - 2026-04-04

### Added
- **Laravel 12 Support**: Added compatibility for Laravel 12.x
- **PHPUnit Test Suite**: Added basic test infrastructure with PHPUnit and Orchestra Testbench
- **GitHub Actions CI**: Updated PHP workflow to include test execution

### Changed
- **Composer Dependencies**: Updated illuminate/* packages to support Laravel 10.*|11.*|12.*
- **Dev Dependencies**: Added phpunit/phpunit, mockery/mockery, and orchestra/testbench for testing
- **PHP Version**: Now requires PHP 8.1+

### Fixed
- **README.md**: Corrected service provider class name from `TwoFactorAuthServiceProvider` to `TwoFactorServiceProvider`

## [1.0.3] - 2025-01-14

### Added
- **Multi-Factor Authentication Methods**: Implemented SMS and Email based Two-Factor Authentication methods beyond TOTP
- **SMS Provider Integration**: Added Twilio SMS provider support with configuration options
- **Email Provider Integration**: Added SMTP and Mailgun email provider support
- **Database Schema**: Added migration for phone number, email 2FA, and method selection fields
- **2FA Settings Controller**: Created `TwoFactorSettingsController` for managing user 2FA preferences
- **Enhanced Configuration**: Extended `config/two-factor.php` with SMS/Email provider settings
- **Method Selection**: Added ability for users to choose between TOTP, SMS, and Email 2FA methods
- **Code Generation & Validation**: Implemented SMS/Email code generation and validation logic
- **Provider Abstraction**: Created extensible provider system for SMS/Email services

### Changed
- **Enhanced TwoFactorAuthentication Trait**: Added methods for SMS/Email 2FA management
- **Updated Controllers**: Modified `ConfirmTwoFactorCodeController` to support multiple 2FA methods
- **Database Model**: Extended `TwoFactorAuthentication` model with new fields and casting

### Fixed
- **Configuration Structure**: Improved configuration organization for better extensibility

## [1.0.2] - Previous Release
- Initial TOTP-based Two-Factor Authentication implementation