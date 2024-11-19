<?php

use admin\enums\AdminStatus;

return [
    [
        'id' => 1,
        'username' => 'admin',
        'email' => 'admin@admin.com',
        'auth_key' => 'iwTNae9t34OmnK6l4vT4IeaTk-YWI2Rv',
        'password_hash' => '$2y$13$nJ1WDlBaGcbCdbNC5.5l4.sgy.OMEKCqtDQOdQ2OWpgiKRWYyzzne',
        'password_reset_token' => 't5GU9NwpuGYSfb7FEZMAxqtuz2PkEvv_' . time(),
        'created_at' => time(),
        'updated_at' => time(),
        'status' => AdminStatus::Active->value
    ],
    [
        'id' => 2,
        'username' => 'moderator',
        'email' => 'moderator@moderator.com',
        'auth_key' => 'EdKfXrx88weFMV0vIxuTMWKgfK2tS3Lp',
        'password_hash' => '$2y$13$nJ1WDlBaGcbCdbNC5.5l4.sgy.OMEKCqtDQOdQ2OWpgiKRWYyzzne',
        'password_reset_token' => '4BSNyiZNAuxjs5Mty990c47sVrgllIi_' . time(),
        'created_at' => time(),
        'updated_at' => time(),
        'status' => AdminStatus::Active->value
    ],
    [
        'id' => 3,
        'username' => 'banned',
        'email' => 'banned@admin.com',
        'auth_key' => 'EdKfXrx88weFMV0vIxuTMWKgfK2tS3Lp',
        'password_hash' => '$2y$13$nJ1WDlBaGcbCdbNC5.5l4.sgy.OMEKCqtDQOdQ2OWpgiKRWYyzzne',
        'password_reset_token' => '5day5MMn8SH7t4mVVmG6tNGeGbkbSPz2_' . time(),
        'created_at' => time(),
        'updated_at' => time(),
        'status' => AdminStatus::Inactive->value
    ],
];
