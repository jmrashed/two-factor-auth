<?php

return [
    'title' => 'Two-Factor Authentication',
    'required' => 'Two-Factor Authentication is required.',
    'back' => 'Go back',
    'continue' => 'To continue, open up your Authenticator app and issue your 2FA code.',
    'enable' => 'You need to enable Two-Factor Authentication.',

    'success' => 'The 2FA code has been validated successfully.',

    'fail_confirm' => 'The code to activate Two-Factor Authentication is invalid.',
    'enabled' => 'Two-Factor Authentication has been enabled for your account.',
    'disabled' => 'Two-Factor Authentication has been disabled for your account.',

    'safe_device' => 'We won\'t ask you for Two-Factor Authentication codes in this device for some time.',

    'confirm' => 'Confirm code',
    'switch_on' => 'Go to enable Two-Factor Authentication.',

    'recovery_code' => [
        'used' => 'You have used a Recovery Code. Remember to regenerate them if you have used almost all.',
        'depleted' => 'You have used all your Recovery Codes. Please use alternate authentication methods to continue.',
        'generated' => 'You have generated a new set of Recovery Codes. Any previous set of codes have been invalidated.',
    ],

    'two_factor_authentication' => 'Two-Factor Authentication',
    'totp_method' => 'Authenticator App (TOTP)',
    'sms_method' => 'SMS (Text Message)',
    'email_method' => 'Email Code',
    'scan_qr_code' => 'Scan this QR code with your authenticator app:',
    'secret_key' => 'Or use this key',
    'verify_code' => 'Verify Code',
    'confirm_totp' => 'Confirm TOTP',
    'enable_totp' => 'Enable TOTP',
    'disable_totp' => 'Disable TOTP',
    'totp_enabled' => 'TOTP Two-Factor Authentication has been enabled.',
    'totp_confirmed' => 'TOTP Two-Factor Authentication has been confirmed.',
    'invalid_totp_code' => 'The provided TOTP code is invalid.',
    'totp_disabled' => 'TOTP Two-Factor Authentication has been disabled.',

    'phone_number' => 'Phone Number',
    'send_code' => 'Send Code',
    'verify_sms_code' => 'Verify SMS Code',
    'verify_sms' => 'Verify SMS',
    'phone_verified' => 'Phone number verified!',
    'sms_enabled' => 'SMS Two-Factor Authentication has been enabled.',
    'sms_code_sent' => 'SMS verification code sent.',
    'sms_send_failed' => 'Failed to send SMS verification code.',
    'invalid_sms_code' => 'The provided SMS code is invalid.',
    'sms_disabled' => 'SMS Two-Factor Authentication has been disabled.',

    'verify_email_code' => 'Verify Email Code',
    'verify_email' => 'Verify Email',
    'email_enabled' => 'Email Two-Factor Authentication has been enabled.',
    'email_code_sent' => 'Email verification code sent.',
    'email_send_failed' => 'Failed to send email verification code.',
    'invalid_email_code' => 'The provided email code is invalid.',
    'email_disabled' => 'Email Two-Factor Authentication has been disabled.',

    'save_settings' => 'Save Settings',
    'invalid_action' => 'Invalid action.',

    'current_password' => 'Current Password',
    'invalid_password' => 'The provided password is incorrect.',
    'regenerate_recovery_codes' => 'Regenerate Recovery Codes',
    'recovery_regenerated' => 'A new set of Recovery Codes has been generated. Your previous codes are no longer valid.',
    'recovery_regeneration_cooldown' => 'Please wait :minutes minute(s) before regenerating your Recovery Codes again.',
    'recovery_codes_remaining' => 'Unused Recovery Codes',
];
