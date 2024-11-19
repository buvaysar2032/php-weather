<?php

use admin\components\{consoleRunner\ConsoleRunner, ThemeManager};
use admin\models\UserAdmin;
use admin\modules\modelExportImport\ModelExportImport;
use admin\modules\rbac\filters\AccessControl;
use admin\modules\rbac\Module;
use common\assets\scssConverter\ScssAssetConverter;
use common\components\{Environment, ErrorHandler, helpers\ModuleHelper, Request, UserUrlManager, UserView};
use kartik\grid\Module as GridView;
use ScssPhp\ScssPhp\{Compiler as ScssCompiler, OutputStyle as ScssOutputStyle};
use yii\bootstrap5\Html;
use yii\redis\Session as RedisSession;
use yii\web\Session;

$module = '/admin/';
$basePath = Environment::readEnv('BASE_URI');

$params = array_merge(
    require dirname(__DIR__, 2) . '/common/config/params.php',
    require dirname(__DIR__, 2) . '/common/config/params-local.php',
    require __DIR__ . '/params.php',
    require __DIR__ . '/params-local.php'
);

return [
    'id' => ModuleHelper::ADMIN,
    'name' => 'PROJECT NAME',
    'homeUrl' => $basePath . $module,
    'basePath' => dirname(__DIR__),
    'sourceLanguage' => 'en-US',
    'language' => 'ru-RU',

    'controllerNamespace' => 'admin\controllers',

    'aliases' => [

    ],

    'bootstrap' => ['log'],

    'controllerMap' => [

    ],

    'modules' => [
        'gridview' => [
            'class' => GridView::class
        ],
        'model-export-import' => [
            'class' => ModelExportImport::class
        ],
        'rbac' => [
            'class' => Module::class
        ]
    ],

    'components' => [
        'assetManager' => [
            'appendTimestamp' => true,
            'converter' => ScssAssetConverter::class
        ],

        'themeManager' => ThemeManager::class,

        'request' => [
            'class' => Request::class,
            'csrfParam' => '_csrf-admin',
            'scriptUrl' => $basePath . $module,
            'baseUrl' => $basePath . rtrim($module, '/'),
            'csrfCookie' => ['httpOnly' => true, 'path' => $basePath . $module]
        ],

        'consoleRunner' => [
            'class' => ConsoleRunner::class,
            'file' => '@root/yii', // or an absolute path to console file
            'phpBinaryPath' => 'php'
        ],

        'user' => [
            'identityClass' => UserAdmin::class,
            'enableSession' => true,
            'enableAutoLogin' => true,
            'loginUrl' => ['/site/login'],
            'identityCookie' => ['name' => '_identity-admin', 'httpOnly' => true, 'path' => $basePath . $module]
        ],

        'session' => [
            'class' => !empty(Environment::readEnv('REDIS_HOSTNAME'))
            && !empty(Environment::readEnv('REDIS_PORT'))
                ? RedisSession::class
                : Session::class,
            'name' => 'advanced-admin',
            'cookieParams' => ['httpOnly' => true, 'path' => $basePath . $module]
        ],

        'errorHandler' => [
            'class' => ErrorHandler::class,
            'errorAction' => 'site/error'
        ],

        'formatter' => [
            'nullDisplay' => Html::tag('span', 'Не задано', ['class' => 'text-muted'])
        ],

        'view' => [
            'class' => UserView::class
        ],

        'urlManager' => [
            'class' => UserUrlManager::class,
            'hideIndex' => true,
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
                'export/download/<filename:[\d\w\._-]+>' => 'export/download'
            ]
        ]
    ],
    'as access' => [
        'class' => AccessControl::class,
        'allowActions' => [
            'site/captcha',
            'site/health',
            'site/logout',
            'site/switch-theme',
            'debug/*'
        ]
    ],
    'container' => [
        'definitions' => [
            ScssCompiler::class => static function () {
                $compiler = new ScssCompiler();
                if (!YII_ENV_DEV) {
                    $compiler->setOutputStyle(ScssOutputStyle::COMPRESSED);
                }
                return $compiler;
            }
        ]
    ],
    'params' => $params
];
