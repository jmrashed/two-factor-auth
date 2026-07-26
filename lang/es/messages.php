<?php

return [
    'title' => 'Autenticación de Dos Factores',
    'required' => 'Se requiere Autenticación de Dos Factores.',
    'back' => 'Volver',
    'continue' => 'Para continuar, abre tu aplicación de autenticación e introduce tu código 2FA.',
    'enable' => 'Necesitas activar la Autenticación de Dos Factores.',

    'success' => 'El código 2FA se ha validado correctamente.',

    'fail_confirm' => 'El código para activar la Autenticación de Dos Factores no es válido.',
    'enabled' => 'La Autenticación de Dos Factores se ha activado en tu cuenta.',
    'disabled' => 'La Autenticación de Dos Factores se ha desactivado en tu cuenta.',

    'safe_device' => 'No te pediremos códigos de Autenticación de Dos Factores en este dispositivo durante un tiempo.',

    'confirm' => 'Confirmar código',
    'switch_on' => 'Ir a activar la Autenticación de Dos Factores.',

    'recovery_code' => [
        'used' => 'Has usado un Código de Recuperación. Recuerda regenerarlos si has usado casi todos.',
        'depleted' => 'Has usado todos tus Códigos de Recuperación. Usa otro método de autenticación para continuar.',
        'generated' => 'Has generado un nuevo conjunto de Códigos de Recuperación. Los anteriores ya no son válidos.',
    ],

    'two_factor_authentication' => 'Autenticación de Dos Factores',
    'totp_method' => 'Aplicación de Autenticación (TOTP)',
    'sms_method' => 'SMS (Mensaje de Texto)',
    'email_method' => 'Código por Correo',
    'scan_qr_code' => 'Escanea este código QR con tu aplicación de autenticación:',
    'secret_key' => 'O usa esta clave',
    'verify_code' => 'Verificar Código',
    'confirm_totp' => 'Confirmar TOTP',
    'enable_totp' => 'Activar TOTP',
    'disable_totp' => 'Desactivar TOTP',
    'totp_enabled' => 'La Autenticación de Dos Factores por TOTP se ha activado.',
    'totp_confirmed' => 'La Autenticación de Dos Factores por TOTP se ha confirmado.',
    'invalid_totp_code' => 'El código TOTP proporcionado no es válido.',
    'totp_disabled' => 'La Autenticación de Dos Factores por TOTP se ha desactivado.',

    'phone_number' => 'Número de Teléfono',
    'send_code' => 'Enviar Código',
    'verify_sms_code' => 'Verificar Código SMS',
    'verify_sms' => 'Verificar SMS',
    'phone_verified' => '¡Número de teléfono verificado!',
    'sms_enabled' => 'La Autenticación de Dos Factores por SMS se ha activado.',
    'sms_code_sent' => 'Código de verificación SMS enviado.',
    'sms_send_failed' => 'No se pudo enviar el código de verificación SMS.',
    'invalid_sms_code' => 'El código SMS proporcionado no es válido.',
    'sms_disabled' => 'La Autenticación de Dos Factores por SMS se ha desactivado.',

    'verify_email_code' => 'Verificar Código de Correo',
    'verify_email' => 'Verificar Correo',
    'email_enabled' => 'La Autenticación de Dos Factores por Correo se ha activado.',
    'email_code_sent' => 'Código de verificación enviado por correo.',
    'email_send_failed' => 'No se pudo enviar el código de verificación por correo.',
    'invalid_email_code' => 'El código de correo proporcionado no es válido.',
    'email_disabled' => 'La Autenticación de Dos Factores por Correo se ha desactivado.',

    'save_settings' => 'Guardar Configuración',
    'invalid_action' => 'Acción no válida.',

    'current_password' => 'Contraseña Actual',
    'invalid_password' => 'La contraseña proporcionada es incorrecta.',
    'regenerate_recovery_codes' => 'Regenerar Códigos de Recuperación',
    'recovery_regenerated' => 'Se ha generado un nuevo conjunto de Códigos de Recuperación. Los anteriores ya no son válidos.',
    'recovery_regeneration_cooldown' => 'Espera :minutes minuto(s) antes de regenerar tus Códigos de Recuperación de nuevo.',
    'recovery_codes_remaining' => 'Códigos de Recuperación sin usar',
];
