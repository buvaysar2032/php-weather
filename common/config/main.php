<?php

use codemix\streamlog\Target;
use common\components\{DbConnection, Environment, RedisConnection, UserFormatter, UserScreener};
use common\modules\backup\Backup;
use common\modules\log\Log;
use common\modules\mail\Mail;
use common\modules\user\{enums\PasswordRestoreType, Module as User};
use common\widgets\reCaptcha\ReCaptchaConfig;
use kartik\datecontrol\Module as DateControl;
use Pug\Yii\ViewRenderer as PugViewRenderer;
use yii\caching\DbCache;
use yii\i18n\PhpMessageSource;
use yii\log\FileTarget;
use yii\mutex\MysqlMutex;
use yii\rbac\DbManager;
use yii\redis\Mutex;
use yii\symfonymailer\Mailer;


$timeZone = 'Europe/Moscow';

return [
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm' => '@vendor/npm-asset',
    ],
    'vendorPath' => dirname(__DIR__, 2) . '/vendor',
    'components' => [
        'authManager' => [
            'class' => DbManager::class,
            'cache' => !YII_ENV_TEST ? 'cache' : null,
        ],
        'environment' => ['class' => Environment::class],
        'cache' => require __DIR__ . '/cache.php',
        'dbCache' => ['class' => DbCache::class],
        'db' => ['class' => DbConnection::class],
        'queue' => require __DIR__ . '/queue.php',
        'redis' => RedisConnection::class,

        'i18n' => [
            'translations' => [
                'app*' => [
                    'class' => PhpMessageSource::class,
                    'basePath' => '@app/messages',
                    'fileMap' => [
                        'app' => 'app.php',
                        'app/error' => 'error.php',
                    ],
                ],
            ],
        ],
        'reCaptcha' => ['class' => ReCaptchaConfig::class],
        'formatter' => [
            'class' => UserFormatter::class,
            'timeZone' => $timeZone,
            'decimalSeparator' => ',',
            'thousandSeparator' => ' ',
            'currencyCode' => 'RUB',
            'timeFormat' => 'php:H:i:s',
            'dateFormat' => 'php:d.m.Y',
            'datetimeFormat' => 'php:d.m.Y H:i',
        ],

        'mailer' => [
            'class' => Mailer::class,
        ],

        'mutex' => !empty(Environment::readEnv('REDIS_HOSTNAME'))
        && !empty(Environment::readEnv('REDIS_PORT'))
            ? Mutex::class : MysqlMutex::class,

        'view' => [
            'renderers' => [
                'pug' => [
                    'class' => PugViewRenderer::class,
                ],
            ],
        ],

        'screener' => [
            'class' => UserScreener::class,
        ],

        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => FileTarget::class,
                    'levels' => ['error', 'warning'],
                    'except' => ['yii\debug\Module*'],
                ],
                [
                    'class' => Target::class,
                    'url' => 'php://stderr',
                    'levels' => ['error', 'warning'],
                    'logVars' => [],
                ],
            ],
        ],
    ],
    'bootstrap' => ['queue'], // The component registers its own console commands
    'modules' => [
        DateControl::MODULE => [
            'class' => DateControl::class,
            'displaySettings' => [
                DateControl::FORMAT_DATE => 'php:d.m.Y',
                DateControl::FORMAT_TIME => 'php:H:i:s',
                DateControl::FORMAT_DATETIME => 'php:d.m.Y H:i',
            ],
            'saveSettings' => [
                DateControl::FORMAT_DATE => 'php:U', // saves as unix timestamp
                DateControl::FORMAT_TIME => 'php:U',
                DateControl::FORMAT_DATETIME => 'php:U',
            ],
            'autoWidget' => true,
            'autoWidgetSettings' => [
                DateControl::FORMAT_TIME => [
                    'pluginOptions' => [
                        'defaultTime' => false,
                        'showSeconds' => true,
                        'showMeridian' => false,
                        'minuteStep' => 1,
                        'secondStep' => 1,
                    ],
                ],
            ],
            // set your display timezone
            'displayTimezone' => $timeZone,

            // set your timezone for date saved to db
            'saveTimezone' => $timeZone,
        ],
        'log' => [
            'class' => Log::class,
            'defaultRoute' => 'main',
            'enabled' => false,
            'visible' => true,
        ],
//        'notification' => [
//            'class' => Notification::class,
//        ],
        'mail' => [
            'class' => Mail::class,
            'mailerOptions' => [
                'delay' => 3,
                'enableMailerLogging' => true,
                'useFileTransport' => false,
                'pushToQueue' => false,
            ],
        ],
        'user' => [
            'class' => User::class,
            'enableEmailVerification' => true,
            'autoSendVerificationEmail' => false,
            'verificationEmailTemplate' => 'email-confirm',
            'enableSocAuthorization' => true,
            'registerIfNot' => true,
            'autoVerifyEmailFromSocNet' => true,
            'enableRedirectToSignup' => false, // TODO
            'enablePasswordRestore' => true,
            'passwordRestoreType' => PasswordRestoreType::ViaToken,
            'passwordSendTemplate' => 'passwordSend',
            'passwordTokenTemplate' => 'passwordResetToken',
            'updateFields' => ['email', 'username', 'first_name', 'middle_name', 'last_name', 'phone'],
            'sendVerificationMessageIfEmailIsChanged' => false,
        ],
        'backup' => [
            'class' => Backup::class,
        ],
    ],
];
