<?php

namespace common\modules\user\social\models;

use Exception;
use Yii;
use yii\helpers\{ArrayHelper, Json};
use yii\httpclient\{Client, Response};

/**
 * Класс для OAuth 2.0 в Google
 *
 * @package user\social\models
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
final class Ggl extends SocBase
{
    /**
     * {@inheritdoc}
     */
    public static string $soc_name = 'ggl';

    /**
     * {@inheritdoc}
     */
    public string|float $ver = 2;

    /**
     * {@inheritdoc}
     */
    public string $scope = 'openid profile email';

    /**
     * {@inheritdoc}
     */
    public function init(): void
    {
        $this->client_id = Yii::$app->environment->GGL_CLIENT;
        $this->client_secret = Yii::$app->environment->GGL_SECRET;
        parent::init();
    }

    /**
     * {@inheritdoc}
     */
    public function getLoginUrl(): string
    {
        return "https://accounts.google.com/o/oauth2/v$this->ver/auth?" .
            http_build_query([
                'scope' => $this->scope,
                'redirect_uri' => $this->redirectUri,
                'response_type' => 'code',
                'client_id' => $this->client_id
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getAccessTokenUrl(): string
    {
        return 'https://www.googleapis.com/oauth2/v4/token';
    }

    /**
     * {@inheritdoc}
     */
    public function getUserUrl(): string
    {
        return 'https://www.googleapis.com/oauth2/v2/userinfo';
    }

    /**
     * {@inheritdoc}
     * @throws Exception
     */
    public function getAuthArgs(): array
    {
        $client = new Client();
        $get_params = Yii::$app->request->get();
        $code = ArrayHelper::getValue($get_params, 'code');
        $params = [
            'code' => $code,
            'client_id' => $this->client_id,
            'client_secret' => $this->client_secret,
            'redirect_uri' => $this->redirectUri,
            'grant_type' => 'authorization_code',
        ];
        return [$this->accessTokenUrl, $client, $params, 'POST'];
    }

    /**
     * Получение параметров для доступа к информации пользователя
     *
     * @throws Exception
     */
    public function getUserArgs(Response $response): array
    {
        $access_token = $this->getAccessTokenFromResponse($response);
        $client = new Client();
        $params = ['access_token' => $access_token];
        return [$this->userUrl, $client, $params];
    }

    /**
     * Получение токена доступа из ответа соц. сети
     *
     * @throws Exception
     */
    public function getAccessTokenFromResponse(Response $response): string
    {
        $content = Json::decode($response->content);
        return ArrayHelper::getValue($content, 'access_token');
    }

    /**
     * Получение id пользователя из ответа соц. сети
     */
    public function getUserIdFromResponse(Response $response): null
    {
        return null;
    }

    /**
     * Получение данных пользователя из ответа соц. сети
     */
    public function getUserDataFromResponse(Response $response): array
    {
        return Json::decode($response->content);
    }
}