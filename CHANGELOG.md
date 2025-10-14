# Changelog

## Unreleased

### Added
- Implemented SMS and Email based Two-Factor Authentication methods.
- Added configuration options for SMS/Email providers in `config/two-factor.php`.
- Implemented methods for sending and verifying codes via SMS/Email in `src/TwoFactor.php` and `src/TwoFactorAuthentication.php`.
- Created `resources/views/settings.blade.php` for 2FA method management.
- Updated `src/Http/Controllers/TwoFactorSettingsController.php` to handle 2FA settings.
- Added routes for 2FA settings in `src/TwoFactorServiceProvider.php`.
- Added new translation keys for SMS/Email 2FA in `lang/en/messages.php`.
- Modified `resources/views/login.blade.php` and `resources/views/confirm.blade.php` to allow 2FA method selection.
- Updated `src/TwoFactorLoginHelper.php` to handle dynamic 2FA method validation.