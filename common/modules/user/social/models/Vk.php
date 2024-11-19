<?php

namespace common\modules\user\social\models;

use Exception;
use Yii;
use yii\helpers\ArrayHelper;
use yii\httpclient\{Client, Response};

/**
 * Класс для OAuth 2.0 в ВКонтакте
 *
 * @package user\social\models
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
final class Vk extends SocBase
{
    /**
     * {@inheritdoc}
     */
    public static string $soc_name = 'vk';

    /**
     * {@inheritdoc}
     */
    public string|float $ver = 5.87;

    /**
     * {@inheritdoc}
     */
    public string $fields = 'uid,first_name,last_name,photo_big,sex,about,bdate';

    /**
     * {@inheritdoc}
     */
    public string $scope = 'friends,email,phone,notifications';

    /**
     * {@inheritdoc}
     */
    public function init(): void
    {
        $this->client_id = Yii::$app->environment->VK_CLIENT;
        $this->client_secret = Yii::$app->environment->VK_SECRET;
        parent::init();
    }

    /**
     * {@inheritdoc}
     */
    public function getLoginUrl(): string
    {
        return 'https://oauth.vk.com/authorize?' .
            http_build_query([
                'client_id' => $this->client_id,
                'display' => 'popup',
                'redirect_uri' => $this->redirectUri,
                'response_type' => 'code',
                'v' => $this->ver,
                'scope' => $this->scope
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getAccessTokenUrl(): string
    {
        return 'https://oauth.vk.com/access_token';
    }

    /**
     * {@inheritdoc}
     */
    public function getUserUrl(): string
    {
        return 'https://api.vk.com/method/users.get';
    }

    /**
     * {@inheritdoc}
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
     * @return mixed
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
        return $response->data['response'][0];
    }
}