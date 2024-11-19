<?php

namespace common\modules\user\social\notification;

use common\modules\user\social\models\Fb;
use yii\base\InvalidConfigException;
use yii\httpclient\{Client, Exception};

/**
 * Реализация отправки уведомлений в Facebook
 *
 * @package user\social\notification
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 *
 * @property-read string $methodUrl
 * @property-read array  $methodParams
 */
final class FbNotification extends NotificationBase
{
    /**
     * Отправка уведомления пользователю в соц. сети
     *
     * @throws InvalidConfigException
     * @throws Exception
     */
    public function send(): bool
    {
        $url = $this->methodUrl;
        $url = str_replace('{user_id}', $this->user->getSocialNetworkById(Fb::$soc_name)->user_auth_id, $url);
        $client = new Client();
        $response = $this->sendRequest($url, $client, $this->methodParams, 'POST');
        if (empty($response['error'])) {
            return true;
        }
        return false;
    }

    /**
     * Получение ссылки на метод отправки уведомления.
     */
    public function getMethodUrl(): string
    {
        return 'https://graph.facebook.com/{user_id}/notifications';
    }

    /**
     * Получение массива параметров для уведомления
     */
    public function getMethodParams(): array
    {
        $href = '';
        $template = $this->message;
        return [$href, $template];
    }
}