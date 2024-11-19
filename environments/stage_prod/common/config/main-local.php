<?php

use yii\symfonymailer\Mailer;

return [
    'components' => [
        'mailer' => [
            'class' => Mailer::class,
            'viewPath' => '@common/mail'
        ]
    ]
];
