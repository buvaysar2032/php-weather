<?php

use common\components\{Request, UserUrlManager};

return yii\helpers\ArrayHelper::merge(
    require __DIR__ . '/main.php',
    require __DIR__ . '/main-local.php',
    require __DIR__ . '/test.php',
    require __DIR__ . '/test-local.php',
    [
        'components' => [
            'request' => [
                'class' => Request::class,
                // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
                'cookieValidationKey' => '',
            ],
            'urlManager' => [
                'class' => UserUrlManager::class
            ]
        ]
    ]
);