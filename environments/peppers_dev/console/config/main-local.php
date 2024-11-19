<?php

use yii\gii\Module as GiiModule;

return [
    'bootstrap' => ['gii'],
    'modules' => [
        'gii' => GiiModule::class
    ],
    'components' => [
        'urlManager' => [
            'baseUrl' => 'https://localhost' // specify for correct console methods working
        ]
    ]
];
