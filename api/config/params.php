<?php

return [
    'adminEmail' => 'admin@example.com',

    // >>> ADMIN/INFO >>>

    'memory-limit' => '128M',
    'upload-max-filesize' => '10M',

    // <<< ADMIN/INFO <<<

    // >>> API SETTINGS >>>

    // Метод восстановления пароля. Должен быть активен один из списка ниже. По умолчанию - token
    'passwordRestoreType' => 'token', // 1. Отправляем
//    'passwordRestoreType' => 'generate', // 2. Отправляем сгенерированный пароль пользователю

    'signup' => [ // Регистрация
        'enabled_clients' => [
            'email-password' => true
        ],
        'require' => [
//            'rules_accepted' => true, // Необходимо согласиться с правилами
        ],
        'unique' => [
            'email' => true // Почта должна быть уникальной
        ]
    ],

    // Ограничение запросов
    'request_limits' => [

        'app-user_reg' => [
            'request_cooldown' => 2,
//            'permanent_block_after_max_level' => true, // Заблокировать пользователя как кончатся уровни блокировки
            'blocking_levels' => [ // Защита от перебора
                [
                    'max_errors' => 3, // Кол-во допустимых ошибок за
                    'error_period' => 5, // Индивидуальный период для ошибок (сек)

                    'max_bf_values' => 3, // Кол-во разных значений за
                    'bf_period' => 10, // Индивидуальный период для перебора (сек)

                    'period' => 5, // Если не задан индивидуальный - берется общий период (сек)

                    'cooldown' => 30 // Задержка блокировки (сек)
//                    'cooldown' => 10, // !!!
                ],
                [
                    'max_errors' => 3,
                    'max_bf_values' => 3,
                    'period' => 5,
                    'cooldown' => 60 * 3 // 3 минуты
//                    'cooldown' => 10, // !!!
                ],
                [
                    'max_errors' => 5,
                    'max_bf_values' => 3,
                    'period' => 5,
                    'cooldown' => 60 * 60 // час
//                    'cooldown' => 10, // !!!
                ],
            ]
        ],

        // >>> Example >>>
        // Тупо ограничение по частоте запросов
        'example_limit-frequency' => [
            'request_cooldown' => 10
        ],

        'example_limit' => [ // Контроллер_Экшн
//            'request_cooldown' => 10, // Ограничение на частоту запросов к методу
            'permanent_block_after_max_level' => true, // Заблокировать пользователя как кончатся уровни блокировки
            'blocking_levels' => [ // Защита от перебора
                [
                    'max_errors' => 3, // Кол-во допустимых ошибок за
                    'error_period' => 5, // Индивидуальный период для ошибок (сек)

                    'max_bf_values' => 3, // Кол-во разных значений за
                    'bf_period' => 10, // Индивидуальный период для перебора (сек)

                    'period' => 5, // Если не задан индивидуальный - берется общий период (сек)

                    'cooldown' => 30 // Задержка блокировки (сек)
//                    'cooldown' => 10, // !!!
                ],
                [
                    'max_errors' => 3,
                    'max_bf_values' => 3,
                    'period' => 5,
                    'cooldown' => 60 * 3 // 3 минуты
//                    'cooldown' => 10, // !!!
                ],
                [
                    'max_errors' => 5,
                    'max_bf_values' => 3,
                    'period' => 5,
                    'cooldown' => 60 * 60 // час
//                    'cooldown' => 10, // !!!
                ],
            ]
        ]
        // <<< Example <<<
    ],

    // <<< API SETTINGS <<<
];
