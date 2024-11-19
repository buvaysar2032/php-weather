<?php

namespace common\modules\user\social\models;

use Exception;
use Yii;
use yii\helpers\ArrayHelper;
use yii\httpclient\{Client, Response};

/**
 * Класс для OAuth 2.0 в Одноклассники
 *
 * @package user\social\models
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 *
 * @property-read array  $authArgs
 * @property-read string $loginUrl
 * @property-read string $accessTokenUrl
 * @property-read string $userUrl
 */
final class Ok extends SocBase
{
    /**
     * {@inheritdoc}
     */
    public static string $soc_name = 'ok';

    /**
     * {@inheritdoc}
     */
    public string|float $ver = 5.85;

    /**
     * {@inheritdoc}
     */
    public string $scope = 'VALUABLE_ACCESS,GET_EMAIL,LONG_ACCESS_TOKEN,EMAIL';

    /**
     * {@inheritdoc}
     */
    public function init(): void
    {
        $this->client_id = Yii::$app->environment->OK_CLIENT;
        $this->client_secret = Yii::$app->environment->OK_SECRET;
        $this->client_public = Yii::$app->environment->OK_PUBLIC;
        parent::init();
    }

    /**
     * {@inheritdoc}
     */
    public function getLoginUrl(): string
    {
        return 'https://connect.ok.ru/oauth/authorize?' .
            http_build_query([
                'client_id' => $this->client_id,
                'scope' => $this->scope,
                'response_type' => 'code',
                'redirect_uri' => $this->redirectUri
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getAccessTokenUrl(): string
    {
        return 'https://api.ok.ru/oauth/token.do';
    }

    /**
     * {@inheritdoc}
     */
    public function getUserUrl(): string
    {
        return 'https://api.ok.ru/fb.do';
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthArgs(): array
    {
        $client = new Client();
        $get_params = Yii::$app->request->get();
        $code = ArrayHelper::getValue($get_params, 'code');
        $params = [
            'client_id' => $this->client_id,
            'client_secret' => $this->client_secret,
            'redirect_uri' => $this->redirectUri,
            'code' => $code,
            'grant_type' => 'authorization_code'
        ];
        $method = 'POST';
        return [$this->accessTokenUrl, $client, $params, $method];
    }

    /**
     * {@inheritdoc}
     * @throws Exception
     */
    public function getUserArgs(Response $response): array
    {
        $access_token = $this->getAccessTokenFromResponse($response);
        $client = new Client();
        $params = [
            'application_key' => $this->client_public,
            'format' => 'json',
            'method' => 'users.getCurrentUser'
        ];
        $params['sig'] = $this->getSig($params, $access_token, $this->client_secret);
        $params['access_token'] = $access_token;
        return [$this->userUrl, $client, $params];
    }

    /**
     * {@inheritdoc}
     * @throws Exception
     */
    public function getAccessTokenFromResponse(Response $response): string
    {
        return ArrayHelper::getValue($response->data, 'access_token');
    }

    /**
     * Расчет сигнатуры с MD5(access_token + application_secret_key)
     */
    private function getSig(array $vars, string $access_token, string $secret): string
    {
        ksort($vars);
        $params = '';
        foreach ($vars as $key => $value) {
            $params .= $key . '=' . $value;
        }
        return md5($params . md5($access_token . $secret));
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
     */
    public function getUserDataFromResponse(Response $response): array
    {
        return $response->data;
    }

    /**
     * Расчет подписи запроса с secret_key
     */
    private function getSessionSig(array $vars, string $secret): string
    {
        ksort($vars);
        $params = '';
        foreach ($vars as $key => $value) {
            $params .= $key . '=' . $value;
        }
        return md5($params . $secret);
    }
}