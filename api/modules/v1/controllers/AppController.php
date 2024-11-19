<?php

namespace api\modules\v1\controllers;

use api\behaviors\returnStatusBehavior\ReturnStatusBehavior;
use common\models\AppActiveRecord;
use common\modules\user\{helpers\UserHelper, models\User};
use Exception;
use OpenApi\Attributes\{Info, Property, Schema, SecurityScheme, Server};
use Throwable;
use Yii;
use yii\base\Arrayable;
use yii\bootstrap5\Html;
use yii\data\ActiveDataProvider;
use yii\filters\{auth\HttpBearerAuth, Cors};
use yii\helpers\{ArrayHelper, Json};
use yii\rest\ActiveController;
use yii\web\{HttpException, IdentityInterface, Response};

/**
 * Class AppController
 *
 * @package controllers
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 *
 * @property bool|string                 $path
 * @property IdentityInterface|User|null $identity
 *
 * Методы класса ReturnStatusBehavior
 * @see     ReturnStatusBehavior
 * @method array returnSuccess(yii\base\Arrayable|array|string $data = null, string $header = 'data', array|string $links = null) Получение сообщения об успехе 200
 * @method array returnError(yii\base\Arrayable|array|string $error = null, yii\base\Arrayable|array|string $error_description = null, int $statusCode = 500) Получение сообщения об ошибке 500
 * @method array returnErrorBadRequest() Получение сообщение о плохом запросе 400
 * @method array returnErrorUserIsNotLoggedIn() Получение ошибки авторизации 401
 * @method array returnUserNotFoundError() Получение ошибки о ненайденном пользователе 401
 * @method array returnActionError() Получение ошибки "Страница не найдена" 405
 * @method array getDBError($error) Получение ошибки БД 500
 */
#[Info(version: '1.0', title: 'Application API')]
#[Server(url: '/api/v1', description: 'current')]
#[SecurityScheme(
    securityScheme: 'bearerAuth',
    type: 'http',
    name: 'bearerAuth',
    in: 'header',
    bearerFormat: 'JWT',
    scheme: 'bearer'
)]
abstract class AppController extends ActiveController
{
    /**
     * {@inheritdoc}
     */
    public $modelClass = AppActiveRecord::class;

    /**
     * {@inheritdoc}
     * @throws Exception
     */
    public function behaviors(): array
    {
        return ArrayHelper::merge(parent::behaviors(), [
            'corsFilter' => [
                'class' => Cors::class,
                'cors' => [
                    'Origin' => static::allowedDomains(),
                    'Access-Control-Request-Method' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'HEAD', 'OPTIONS'],
                    'Access-Control-Request-Headers' => ['*'],
                    'Access-Control-Allow-Credentials' => true,
                    'Access-Control-Max-Age' => 3600,
                ],
            ],
            'contentNegotiator' => [
                'formats' => [
                    'text/html' => Response::FORMAT_JSON,
                    'application/json' => Response::FORMAT_JSON,
                ],
            ],
            'auth' => ['class' => HttpBearerAuth::class],
            'returnStatus' => ['class' => ReturnStatusBehavior::class],
        ]);
    }

    /**
     * Получить список разрешенных доменов
     *
     * @throws Exception
     */
    public static function allowedDomains(): array
    {
        $allowedDomains = [];
        if (YII_ENV_DEV) {
            $allowedDomains[] = 'http://localhost';
            $allowedDomains[] = 'http://localhost:3000';
        }
        $corsDomains = Yii::$app->environment->CORS_DOMAINS
            ? explode(',', Yii::$app->environment->CORS_DOMAINS) : [];
        foreach ($corsDomains as $corsDomain) {
            if (!preg_match('#https?://#', $corsDomain)) {
                $corsDomain = 'https://' . $corsDomain;
            }
            $allowedDomains[] = rtrim($corsDomain, '/');
        }
        return $allowedDomains;
    }

    /**
     * Вернуть JS отклик
     */
    final public function returnOpenerResponse(array|Arrayable $response, string $domain = null): string
    {
        if (is_null($domain)) {
            $domain = Yii::$app->request->hostInfo;
        }
        Yii::$app->response->format = Response::FORMAT_HTML;
        $data = Json::encode($response);
        return Html::script(
            <<<JS
                const data = JSON.parse('$data');
                try {
                    window.opener.postMessage(data, '$domain');
                } catch (e) {}
                try {
                    parent.postMessage(data, '$domain');
                } catch (e) {}
                JS,
        );
    }

    /**
     * Получить авторизованного пользователя (identity)
     *
     * @throws HttpException
     */
    final public function getIdentity(): ?IdentityInterface
    {
        return UserHelper::checkUserStatus(Yii::$app->user->identity);
    }

    /**
     * {@inheritdoc}
     */
    final public function runAction($id, $params = []): mixed
    {
        // Проверка ключа идемпотентности
        if (($response = $this->checkIdempotencyKey()) === false) {
            // Транзакция для любого POST запроса
            if (Yii::$app->request->isPost && ($transaction = Yii::$app->db->beginTransaction())) {
                try {
                    $response = parent::runAction($id, $params);
                    $transaction->commit();
                } catch (Exception|Throwable $e) {
                    $transaction->rollBack();
                    throw $e;
                }
            } else {
                $response = parent::runAction($id, $params);
            }
            // Сохранение ключа идемпотентности
            $this->saveIdempotenceResponse($response);
        }
        return $response;
    }

    private function checkIdempotencyKey()
    {
        if (
            Yii::$app->request->isPost
            && ($idempotencyKey = Yii::$app->request->headers->get('Idempotency-Key'))
            && ($response = Yii::$app->cache->get("Idempotency-$idempotencyKey"))
            && ($statusCode = Yii::$app->cache->get("Idempotency-StatusCode-$idempotencyKey"))
        ) {
            Yii::$app->response->statusCode = $statusCode;
            return $response;
        }
        return false;
    }

    private function saveIdempotenceResponse($response): void
    {
        if (
            Yii::$app->request->isPost
            && ($idempotencyKey = Yii::$app->request->headers->get('Idempotency-Key'))
        ) {
            Yii::$app->cache->multiAdd([
                "Idempotency-$idempotencyKey" => $response,
                "Idempotency-StatusCode-$idempotencyKey" => Yii::$app->response->statusCode,
            ], 600);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function actions(): array
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function verbs(): array
    {
        return [];
    }

    #[Schema(schema: 'Pagination', properties: [
        new Property(property: 'totalCount', type: 'integer', example: 1),
        new Property(property: 'page', type: 'integer', example: 1),
        new Property(property: 'pageSize', type: 'integer', example: 20),
        new Property(property: 'pageCount', type: 'integer', example: 1),
    ])]
    final public function getPaginationMeta(ActiveDataProvider $dataProvider): array
    {
        return [
            'totalCount' => $dataProvider->totalCount,
            'page' => $dataProvider->pagination->page + 1 ?? 0,
            'pageSize' => $dataProvider->pagination->pageSize,
            'pageCount' => $dataProvider->pagination->pageCount,
        ];
    }
}
