<?php

use admin\modules\rbac\ConsoleModule;
use common\components\{helpers\ModuleHelper, UserUrlManager};
use console\controllers\UserAdminController;
use yii\console\{controllers\FixtureController,
    controllers\MigrateController,
    controllers\ServeController,
    ErrorHandler};

$params = array_merge(
    require dirname(__DIR__, 2) . '/common/config/params.php',
    require dirname(__DIR__, 2) . '/common/config/params-local.php',
    require __DIR__ . '/params.php',
    require __DIR__ . '/params-local.php'
);

return [
    'id' => ModuleHelper::CONSOLE,
    'basePath' => dirname(__DIR__),
    'sourceLanguage' => 'en-US',
    'language' => 'ru-RU',
    'controllerNamespace' => 'console\controllers',
    'bootstrap' => ['log'],
    'aliases' => [],
    'controllerMap' => [
        'migrate' => [
            'class' => MigrateController::class,
            'color' => DIRECTORY_SEPARATOR === '\\' ?: null,
            'generatorTemplateFiles' => [
                'create_table' => '@console/views/createTableMigration.php',
                'drop_table' => '@yii/views/dropTableMigration.php',
                'add_column' => '@yii/views/addColumnMigration.php',
                'drop_column' => '@yii/views/dropColumnMigration.php',
                'create_junction' => '@yii/views/createTableMigration.php'
            ],
            'migrationPath' => ['@app/migrations', '@yii/rbac/migrations'],
            'migrationNamespaces' => [
                'yii\queue\db\migrations',
                'common\modules\user\migrations',
                'common\modules\mail\migrations',
//                'common\modules\notification\migrations',
//                'common\modules\log\migrations',
                'admin\modules\modelExportImport\migrations'
            ]
        ],
        'fixture' => [
            'class' => FixtureController::class,
            'namespace' => 'common\fixtures'
        ],
        'admin' => [
            'class' => UserAdminController::class
        ],
        'serve' => [
            'class' => ServeController::class,
            'docroot' => '@root/htdocs'
        ],
    ],
    'modules' => [
        'rbac' => [
            'class' => ConsoleModule::class
        ]
    ],
    'components' => [
        'errorHandler' => [
            'class' => ErrorHandler::class
        ],
        'formatter' => [
            'nullDisplay' => 'null'
        ],

        'urlManager' => [
            'class' => UserUrlManager::class,
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
//                '' => 'site/index',
            ]
        ]
    ],
    'params' => $params
];
