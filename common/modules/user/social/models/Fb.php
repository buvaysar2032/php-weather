<?php

namespace common\modules\user\social\models;

use Exception;
use Yii;
use yii\helpers\ArrayHelper;
use yii\httpclient\{Client, Response};

/**
 * Класс для OAuth 2.0 в Facebook
 *
 * @package user\social\models
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
final class Fb extends SocBase
{
    /**
     * {@inheritdoc}
     */
    public static string $soc_name = 'fb';

    /**
     * {@inheritdoc}
     */
    public string|float $ver = 3.1;

    /**
     * {@inheritdoc}
     */
    public string $fields = 'id,name,age_range,first_name,last_name,email';

    /**
     * {@inheritdoc}
     */
    public function init(): void
    {
        $this->client_id = Yii::$app->environment->FB_CLIENT;
        $this->client_secret = Yii::$app->environment->FB_SECRET;
        parent::init();
    }

    /**
     * {@inheritdoc}
     */
    public function getLoginUrl(): string
    {
        return "https://www.facebook.com/v$this->ver/dialog/oauth?" .
            http_build_query([
                'client_id' => $this->client_id,
                'display' => 'popup',
                'redirect_uri' => $this->redirectUri,
                'response_type' => 'code',
                'scope' => $this->scope
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getAccessTokenUrl(): string
    {
        return "https://graph.facebook.com/v$this->ver/oauth/access_token";
    }

    /**
     * {@inheritdoc}
     */
    public function getUserUrl(): string
    {
        return "https://graph.facebook.com/v$this->ver/me";
    }

    /**
     * {@inheritdoc}
     *
     * @throws Exception
     */
    public function getUserArgs(Response $response): array
    {
        $user_id = $this->getUserIdFromResponse($response);
        $access_token = $this->getAccessTokenFromResponse($response);
        $client = new Client();
        $params = [
            'user_id' => $user_id,
            'access_token' => $access_token,
            'fields' => $this->fields,
            'v' => $this->ver,
        ];
        return [$this->userUrl, $client, $params];
    }

    /**
     * {@inheritdoc}
     *
     * @throws Exception
     */
    public function getUserIdFromResponse(Response $response): mixed
    {
        return ArrayHelper::getValue($response->data, 'user_id');
    }

    /**
     * {@inheritdoc}
     *
     * @throws Exception
     */
    public function getAccessTokenFromResponse(Response $response): string
    {
        return ArrayHelper::getValue($response->data, 'access_token');
    }

    /**
     * {@inheritdoc}
     */
    public function getUserDataFromResponse(Response $response): array
    {
        return $response->data;
    }
}