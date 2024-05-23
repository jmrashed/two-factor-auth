# Laravel Two-Factor Authentication (2FA)

[![Stable Version](https://img.shields.io/packagist/v/jmrashed/two-factor-auth)](https://packagist.org/packages/jmrashed/two-factor-auth)
[![License](https://img.shields.io/github/license/jmrashed/two-factor-auth)](LICENSE.md)
[![Total Downloads](https://img.shields.io/packagist/dt/jmrashed/two-factor-auth)](https://packagist.org/packages/jmrashed/two-factor-auth)
[![GitHub Stars](https://img.shields.io/github/stars/jmrashed/two-factor-auth)](https://github.com/jmrashed/two-factor-auth/stargazers)
[![GitHub Forks](https://img.shields.io/github/forks/jmrashed/two-factor-auth)](https://github.com/jmrashed/two-factor-auth/network/members)

## Overview

The **Laravel Two-Factor Authentication (2FA)** package provides a seamless way to enhance the security of Laravel applications by implementing two-factor authentication for user authentication. With support for multiple authentication methods and customizable configuration options, this package offers developers a robust solution to protect user accounts against unauthorized access.

### Key Features

- **Multiple 2FA Methods**: Supports various two-factor authentication methods, including SMS, email, TOTP (Time-Based One-Time Password), and more.

- **Customizable Configuration**: Allows developers to configure 2FA settings according to their application's requirements, including enabling/disabling specific authentication methods, setting up recovery options, and defining access policies.

- **Seamless Integration**: Integrates seamlessly with Laravel's authentication system, making it easy to add two-factor authentication to existing Laravel applications without extensive modifications.

- **User-Friendly Interfaces**: Provides intuitive user interfaces for enabling, disabling, and managing two-factor authentication settings, ensuring a smooth user experience for account security management.

- **Comprehensive Documentation**: Offers comprehensive documentation with installation instructions, usage guidelines, configuration options, and best practices for implementing and managing two-factor authentication in Laravel applications.

### Why Use Laravel 2FA

- **Enhanced Security**: Protects user accounts against common security threats such as password breaches, phishing attacks, and unauthorized access attempts.

- **Compliance Requirements**: Helps meet compliance requirements for securing sensitive user data and ensuring regulatory compliance in various industries.

- **User Trust and Confidence**: Builds trust and confidence among users by providing an additional layer of security for their accounts, leading to increased user satisfaction and loyalty.

### Getting Started

To get started with Laravel 2FA, simply install the package via Composer and follow the installation and configuration instructions provided in the documentation. With just a few simple steps, you can enhance the security of your Laravel applications and provide users with peace of mind knowing their accounts are well-protected.

## Requirements

- PHP >= 8.1
- Laravel >= 10.x
- Composer (for package installation)

Ensure that your server environment meets the following requirements:

- **PHP Version**: The package requires PHP version 8.1 or later to run properly. You can check your PHP version by running `php -v` in your terminal.

- **Laravel Version**: The package is compatible with Laravel version 10.x or later. Make sure your Laravel application is using the specified version or later for seamless integration.

- **Composer**: Composer is required to install the package and manage its dependencies. If you haven't installed Composer yet, you can download and install it from [composer](https://getcomposer.org/).

Before proceeding with the installation, verify that your server environment meets these requirements to ensure compatibility and smooth operation of the two-factor authentication package.

## Installation

You can install the **Laravel Two-Factor Authentication (2FA)** package via Composer by running the following command in your terminal:

```bash
composer require jmrashed/two-factor-auth
```

After installing the package, Laravel will automatically discover the service provider. If you're using Laravel version 5.5 or higher, the package will be auto-discovered. For older versions of Laravel, you may need to manually register the service provider in your `config/app.php` file:

```php
'providers' => [
    // Other Service Providers
    Jmrashed\TwoFactorAuth\TwoFactorAuthServiceProvider::class,
],
```

### How it Works

The **Laravel Two-Factor Authentication (2FA)** package introduces a flexible mechanism to enhance user authentication with an additional layer of security. Here's how it functions:

#### 1. Contract Integration

The package seamlessly integrates a contract to determine whether a user, following successful credential validation, should employ Two-Factor Authentication (2FA) as a supplementary authentication measure.

#### 2. Custom Views and Helper Functions

Included in the package are custom views and helper functions dedicated to managing Two-Factor Authentication (2FA) during login attempts. These resources facilitate the implementation of 2FA without the need for additional middleware or new guards. However, advanced users have the option to customize the authentication process manually if desired.

#### 3. No Middleware Dependency

Unlike some alternatives, this package operates without a dependency on middleware. While it provides a simplified setup, it also allows users the flexibility to configure the authentication process according to their specific requirements, should they choose to do so manually.

#### 4. Extensibility

The package is designed to be extensible, accommodating potential future enhancements and customizations. It offers a foundation upon which users can build additional features or integrate with other parts of their Laravel application.

## Setup

### Step - 01

Begin by installing the necessary components into your Laravel application, including migrations, translations, views, and configuration, using the following Artisan command:

```shell
php artisan two-factor-auth:install
```

   > **Tip:**
   >
   > You can customize the migration before running it, such as adding new columns or changing the table name. Refer to the [migration documentation](MIGRATIONS.md) for more details.

   After installation, migrate the database tables using the standard Artisan migrate command:

```shell
php artisan migrate
```

### Step - 02

Integrate the `Two-Factor Authentication` features into your User model or any other model where you want to enable Two-Factor Authentication. Simply add the `TwoFactorAuthenticatable` contract and the `TwoFactorAuthentication` trait to the model as shown below:

```php
namespace App;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Jmrashed\TwoFactorAuth\TwoFactorAuthentication;
use Jmrashed\TwoFactorAuth\Contracts\TwoFactorAuthenticatable;

class User extends Authenticatable implements TwoFactorAuthenticatable
{
    use TwoFactorAuthentication;

    // Additional model code...
}
```

   > **Tip:**
   >
   > The `TwoFactorAuthenticatable` contract identifies models using Two-Factor Authentication, while the `TwoFactorAuthentication` trait provides convenient methods for managing Two-Factor Authentication.

That's it! Your application is now equipped to utilize Two-Factor Authentication for enhanced security.

### Enabling Two-Factor Authentication

To enable Two-Factor Authentication for a user, they must synchronize the Shared Secret between their Authenticator app and the application.

   > **Tip:**
   >
   > Popular free Authenticator apps include iOS Authenticator, FreeOTP, Authy, and Google Authenticator, among others.

Start by generating the required data using the `createTwoFactorAuth()` method. This method returns a serializable Shared Secret that can be displayed to the user as a string or QR Code (encoded as SVG) in your view.

```php
use Illuminate\Http\Request;

public function prepareTwoFactor(Request $request)
{
    $secret = $request->user()->createTwoFactorAuth();

    return view('user.2fa', [
        'qr_code' => $secret->toQr(),     // QR Code
        'uri'     => $secret->toUri(),    // "otpauth://" URI
        'string'  => $secret->toString(), // Secret as string
    ]);
}
```

Next, the user must confirm the Shared Secret with a Code generated by their Authenticator app using the `confirmTwoFactorAuth()` method, which will automatically enable Two-Factor Authentication if the code is valid.

```php
use Illuminate\Http\Request;

public function confirmTwoFactor(Request $request)
{
    $request->validate([
        'code' => 'required|numeric'
    ]);
    $activated = $request->user()->confirmTwoFactorAuth($request->code);
    // Additional logic...
}
```

If the user doesn't provide the correct Code, the method will return `false`, prompting them to double-check their device's timezone or generate a new Shared Secret.

### Recovery Codes

Recovery Codes, used as backup authentication methods, are automatically generated each time Two-Factor Authentication is enabled. By default, a collection of ten one-use 8-character codes is created.

You can retrieve these codes using the `getRecoveryCodes()` method.

```php
use Illuminate\Http\Request;

public function confirmTwoFactor(Request $request)
{
    if ($request->user()->confirmTwoFactorAuth($request->code)) {
        return $request->user()->getRecoveryCodes();
    }
    return 'Try again!';
}
```

Ensure you provide users with their Recovery Codes after enabling Two-Factor Authentication and advise them to securely store them. The `generateRecoveryCodes()` method can be used to generate a fresh batch of codes, replacing the previous set.

```php
use Illuminate\Http\Request;

public function showRecoveryCodes(Request $request)
{
    return $request->user()->generateRecoveryCodes();
}
```

   > **Important:**
   >
   > Users should be encouraged to generate new Recovery Codes if they deplete their current set or if they become inactive.

### Logging In

For seamless user authentication, utilize the `Auth2FA` facade provided by the package, which handles the authentication process with support for Two-Factor Authentication.

   > **Tip:**
   >
   > For Laravel UI or Laravel Breeze, refer to the package documentation for specific integration instructions.

In your Login Controller, employ the `Auth2FA::attempt()` method with the user's credentials. If Two-Factor Authentication is required, the user will be prompted to enter their 2FA Code.

```php
use Jmrashed\TwoFactorAuth\Facades\Auth2FA;
use Illuminate\Http\Request;

public function login(Request $request)
{
    // Validate user credentials
     $attempt = Auth2FA::attempt($request->only('email', 'password'));
    if ($attempt) {
        return 'You are logged in!';
    }
    return 'Hey, you should make an account!';
}
```

This package enables TOTP authentication using 6 digits codes. No need for external APIs.

## Authors

This package is maintained by [jmrashed](https://www.github.com/jmrashed), a passionate developer dedicated to enhancing Laravel development experiences.

Feel free to contribute or report issues on [GitHub](https://www.github.com/jmrashed/two-factor-auth). Your feedback and contributions are highly appreciated!

## Contributing

We welcome contributions from everyone! Whether you're fixing a typo, adding a feature, or suggesting improvements, your contributions are valued and appreciated.

### Follow these guidelines

If you're new to contributing to open-source projects, don't worry! Here's how you can get started:

1. Fork the repository.
2. Clone your forked repository to your local machine.
3. Create a new branch for your changes.
4. Make your changes and commit them with clear and descriptive messages.
5. Push your changes to your forked repository.
6. Submit a pull request to the main repository.

### Code of Conduct

Please note that we have a [Code of Conduct](CODE_OF_CONDUCT.md) in place to ensure a welcoming and inclusive environment for everyone. By participating in this project, you agree to abide by its terms.

### Feedback

Your feedback is crucial for the improvement of this package. If you encounter any issues, have ideas for new features, or simply want to share your thoughts, please open an issue on GitHub.

Thank you for your contributions! Together, we can make this package even better.

## Documentation

Explore our comprehensive documentation to learn more about using the Two-Factor Authentication package:

- **[Documentation](https://github.com/jmrashed/two-factor-auth/docs)**: Access detailed guides, tutorials, and API references to understand how to integrate and utilize the Two-Factor Authentication package effectively.

Our documentation is continually updated to provide you with the latest information and best practices. If you have any questions or need further assistance, don't hesitate to reach out or open an issue on GitHub. We're here to help you succeed with your authentication needs.
