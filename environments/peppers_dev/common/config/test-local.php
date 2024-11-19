<?php

use yii\web\User;

return [
    'components' => [
        'user' => [
            'class' => User::class,
            'identityClass' => \common\modules\user\models\User::class,
        ]
    ]
];
