<?php

namespace common\modules\user\social\models;

use common\components\helpers\UserUrl;
use common\modules\user\social\SocInterface;
use Exception;
use Yii;
use yii\base\Component;
use yii\helpers\ArrayHelper;
use yii\httpclient\Client;

/**
 * Базовый класс для OAuth 2.0
 *
 * @package user\social\models
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 *
 * @property-read string $redirectUri    Url для редиректа из соц. сети обратно
 *
 * @property-read string $loginUrl       Url для OAuth авторизации в соц. сети
 * @property-read string $accessTokenUrl Токен доступа к соц. сети
 * @property-read array  $authArgs       Параметры авторизации OAuth
 * @property-read string $userUrl        Url к API соц. сети для получения данных пользователя
 */
abstract class SocBase extends Component implements SocInterface
{
    /**
     * Идентификатор соц. сети
     */
    public static string $soc_name = '';

    /**
     * Версия api соц. сети
     */
    public string|float $ver = '';

    /**
     * Идентификатор приложения в соц. сети
     */
    public string $client_id = '';

    /**
     * Секретный ключ приложения в соц. сети
     */
    public string $client_secret = '';

    /**
     * Открытый ключ приложения
     */
    public string $client_public = '';

    /**
     * Список полей
     */
    public string $fields = '';

    /**
     * Список прав для доступа к api
     */
    public string $scope = '';

    /**
     * Получение Url для редиректа из соц. сети обратно
     *
     * @throws Exception
     */
    final public function getRedirectUri(): string
    {
        return sprintf('%s/api/%s', Yii::$app->request->hostInfo, Yii::$app->controller->route);
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
            'client_id' => $this->client_id,
            'client_secret' => $this->client_secret,
            'redirect_uri' => $this->redirectUri,
            'code' => $code,
            'v' => $this->ver,
        ];
        return [$this->accessTokenUrl, $client, $params];
    }
}