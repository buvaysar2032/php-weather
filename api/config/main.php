<?php

use api\modules\v1\Module;
use common\components\{Environment, ErrorHandler, helpers\ModuleHelper, Request, UserUrlManager};
use common\modules\user\models\User;
use yii\base\Event;
use yii\redis\Session as RedisSession;
use yii\web\{JsonParser, JsonResponseFormatter, Response, Session};

$module = '/api/';
$basePath = Environment::readEnv('BASE_URI');

$params = array_merge(
    require dirname(__DIR__, 2) . '/common/config/params.php',
    require dirname(__DIR__, 2) . '/common/config/params-local.php',
    require __DIR__ . '/params.php',
    require __DIR__ . '/params-local.php'
);

return [
    'id' => ModuleHelper::API,
    'homeUrl' => $basePath . $module,
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'language' => 'ru-RU',
    'controllerNamespace' => 'api\controllers',
    'aliases' => [],
    'modules' => [
        'v1' => [
            'class' => Module::class,
            'controllerMap' => []
        ]
    ],

    'components' => [
        'assetManager' => [
            'appendTimestamp' => true
        ],

        'request' => [
            'class' => Request::class,
            'csrfParam' => '_csrf-api',
            'enableCsrfValidation' => false, // в REST запросах не работает по умолчанию
            'scriptUrl' => $basePath . $module,
            'baseUrl' => $basePath . rtrim($module, '/'),
            'parsers' => [
                'application/json' => JsonParser::class
            ],
            'csrfCookie' => ['httpOnly' => true, 'path' => $basePath . $module]
        ],

        'user' => [
            'identityClass' => User::class,
            'enableSession' => false,
            // Т. к. авторизация идет только через bearer token, должно быть выключено для того, чтобы избежать блокировки открытых методов API при блокировке пользователя
            'enableAutoLogin' => true,
            'identityCookie' => ['name' => '_identity-api', 'httpOnly' => true, 'path' => $basePath . $module]
        ],

        'session' => [
            'class' => !empty(Environment::readEnv('REDIS_HOSTNAME'))
            && !empty(Environment::readEnv('REDIS_PORT'))
                ? RedisSession::class
                : Session::class,
            'name' => 'advanced-api',
            'cookieParams' => ['httpOnly' => true, 'path' => $basePath . $module]
        ],

        'response' => [
            'formatters' => [
                Response::FORMAT_JSON => [
                    'class' => JsonResponseFormatter::class,
                    'prettyPrint' => YII_DEBUG, // use "pretty" output in debug mode
                    'encodeOptions' => JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
                ]
            ],
            'format' => Response::FORMAT_JSON,
            'on beforeSend' => static function (Event $event) {
                /** @var Response $response */
                $response = $event->sender;
                if (is_array($response->data)) {
                    $response_data = ['success' => $response->isSuccessful];

                    if ($response->data) {
                        $response_data['data'] = $response->data['data'] ?? $response->data;
                        if (is_string($response_data['data'])) {
                            $response_data['data'] = [
                                'name' => 'Unknown',
                                'message' => $response_data['data'],
                            ];
                        }
                        $response_data['data']['status'] = Yii::$app->response->statusCode;
                    }
                    $response->data = $response_data;
                }
            }
        ],

        'errorHandler' => [
            'class' => ErrorHandler::class,
            'on renderException' => static function () {
                $hash = Yii::$app->request->headers->get('X-Request-ID', Yii::$app->security->generateRandomString(8));
                foreach (Yii::$app->log->targets as $target) {
                    $target->prefix = static fn() => "Request-ID=$hash; ";
                }
                Yii::$app->response->headers->set('X-Request-ID', $hash);
            }
        ],

        'urlManager' => [
            'class' => UserUrlManager::class,
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => []
        ]
    ],
    'params' => $params
];
