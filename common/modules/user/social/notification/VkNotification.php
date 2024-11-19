<?php

namespace common\modules\user\social\notification;

use common\modules\user\social\models\Vk;
use Yii;
use yii\base\InvalidConfigException;
use yii\httpclient\{Client, Exception};

/**
 * Реализация отправки уведомлений в ВК
 *
 * @package user\social\notification
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 *
 * @property-read string $methodUrl
 * @property-read array  $methodParams
 */
final class VkNotification extends NotificationBase
{
    /**
     * Отправка уведомления пользователю в соц. сети
     *
     * @throws InvalidConfigException
     * @throws Exception
     */
    public function send(): bool
    {
        $client = new Client();
        $params = $this->methodParams;
        $vk = new Vk();
        $response = $this->sendRequest(
            $vk->getAccessTokenUrl(),
            $client,
            [
                'client_id' => Yii::$app->environment->VK_CLIENT,
                'client_secret' => Yii::$app->environment->VK_SECRET,
                'grant_type' => 'client_credentials',
            ]
        );
        $params['access_token'] = $response->data['access_token'];
        $response = $this->sendRequest($this->methodUrl, $client, $params);
        if (!$response->data['error']) {
            return true;
        }
        return false;
    }

    /**
     * Получение ссылки на метод отправки уведомления.
     */
    public function getMethodUrl(): string
    {
        return 'https://api.vk.com/method/secure.sendNotification';
    }

    /**
     * Получение массива параметров для уведомления
     */
    public function getMethodParams(): array
    {
        $vk = new Vk();
        return [
            'client_secret' => Yii::$app->environment->VK_SECRET,
            'user_id' => $this->user->getSocialNetworkById(Vk::$soc_name)->user_auth_id,
            'message' => $this->message,
            'v' => $vk->ver
        ];
    }
}