<?php

return [
    // >>> ADMIN/INFO >>>
    'adminEmail' => 'admin@example.com',
    'supportEmail' => 'support@example.com',
    'senderEmail' => 'noreply@example.com',
    'senderName' => 'Example.com mailer',
    'user.passwordResetTokenExpire' => 3600,

    // >>> FRAMEWORK/CONFIG >>>
	'required-php-version' => '8.1',
	'required-db-version' => '5.5',
    'memory-limit' => '128M',
    'upload-max-filesize' => '10M',
    'disk-free-space' => '100M',

    // >>> Kartik config >>>
    'bsVersion' => '5',
    'icon-framework' => kartik\icons\Icon::FAS
];
