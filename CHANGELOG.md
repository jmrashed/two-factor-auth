# Changelog

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