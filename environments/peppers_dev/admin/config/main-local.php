<?php

use admin\controllers\AdminController;
use common\models\AppActiveRecord;
use common\modules\gii\generators\{crud\Generator as CrudGenerator, model\Generator as ModelGenerator};
use yii\debug\Module as DebugModule;
use yii\gii\generators\{controller\Generator as ControllerGenerator, model\Generator};
use yii\gii\Module as GiiModule;

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
        'class' => GiiModule::class,
        'allowedIPs' => ['127.0.0.1'],
        'generators' => [
            'model' => [
                'class' => ModelGenerator::class,
                'templates' => [
                    'default' => '@common/modules/gii/templates/model/default'
                ],
                'baseClass' => AppActiveRecord::class,
                'generateJunctionRelationMode' => Generator::JUNCTION_RELATION_VIA_MODEL,
                'ns' => 'common\models',
                'enableI18N' => true,
                'useTablePrefix' => true
            ],
            'crud' => [
                'class' => CrudGenerator::class,
                'templates' => [
                    'default' => '@common/modules/gii/templates/crud/default'
                ],
                'controllerClass' => 'admin\controllers\Controller',
                'modelClass' => 'common\models\\',
                'searchModelClass' => 'common\models\Search',
                'viewPath' => '@admin/views/',
                'baseControllerClass' => AdminController::class,
                'enableI18N' => true,
                'enablePjax' => true
            ],
            'controller' => [
                'class' => ControllerGenerator::class,
                'baseClass' => AdminController::class,
                'controllerClass' => 'admin\controllers\\'
            ]
        ]
    ];
}

return $config;
