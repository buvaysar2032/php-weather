<?php

use api\modules\v1\controllers\AppController;
use yii\debug\Module as DebugModule;
use yii\gii\generators\controller\Generator as ControllerGenerator;
use yii\gii\Module as Gii;

$config = [
    'components' => [
        'request' => [
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => ''
        ]
    ]
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => DebugModule::class,
        'allowedIPs' => ['127.0.0.1', '192.168.50.*', '217.15.151.60', '172.*.0.1']
    ];
    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => Gii::class,
        'allowedIPs' => ['127.0.0.1'],
        'generators' => [
            'controller' => [
                'class' => ControllerGenerator::class,
                'baseClass' => AppController::class,
                'controllerClass' => 'api\v1\controllers\\'
            ]
        ]
    ];
}

return $config;
