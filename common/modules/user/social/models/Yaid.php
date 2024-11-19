<?php

namespace common\modules\user\social\models;

use Exception;
use Yii;
use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\httpclient\{Client, Response};

/**
 * Класс для OAuth 2.0 в Yandex
 *
 * @package common\modules\user\social\models
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
class Yaid extends SocBase
{
    /**
     * {@inheritdoc}
     */
    public static string $soc_name = 'yaid';

    /**
     * {@inheritdoc}
     */
    public function init(): void
    {
        $this->client_id = Yii::$app->environment->YAID_CLIENT;
        $this->client_secret = Yii::$app->environment->YAID_SECRET;
        parent::init();
    }

    /**
     * {@inheritdoc}
     */
    public function getLoginUrl(): string
    {
        return 'https://oauth.yandex.ru/authorize?' .
            http_build_query([
                'response_type' => 'code',
                'client_id' => $this->client_id,
                'redirect_uri' => $this->redirectUri
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getAccessTokenUrl(): string
    {
        return 'https://oauth.yandex.ru/token';
    }

    /**
     * {@inheritdoc}
     */
    public function getUserUrl(): string
    {
        return 'https://login.yandex.ru/info?format=json';
    }

    /**
     * {@inheritdoc}
     * @throws Exception
     */
    public function getAuthArgs(): array
    {
        $request = (new Client())->createRequest();
        $get_params = Yii::$app->request->get();
        $code = ArrayHelper::getValue($get_params, 'code');
        $params = [
            'code' => $code,
            'client_id' => $this->client_id,
            'client_secret' => $this->client_secret,
            'grant_type' => 'authorization_code',
        ];
        return [$this->accessTokenUrl, $request, $params, 'POST'];
    }

    /**
     * {@inheritdoc}
     * @throws InvalidConfigException
     * @throws Exception
     */
    public function getUserArgs(Response $response): array
    {
        $access_token = $this->getAccessTokenFromResponse($response);
        $request = (new Client())->createRequest();
        $request->addHeaders(['Authorization' => "OAuth $access_token"]);
        return [$this->userUrl, $request, []];
    }

    /**
     * {@inheritdoc}
     */
    public function getUserIdFromResponse(Response $response): null
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getUserDataFromResponse(Response $response): array
    {
        return Json::decode($response->content);
    }

    /**
     * {@inheritdoc}
     * @throws Exception
     */
    public function getAccessTokenFromResponse(Response $response): string
    {
        return ArrayHelper::getValue(Json::decode($response->content), 'access_token');
    }
}