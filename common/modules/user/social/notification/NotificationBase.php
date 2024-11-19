<?php

namespace common\modules\user\social\notification;

use common\modules\user\models\User;
use yii\base\Component;
use yii\httpclient\{Client, Exception, Response};
use yii\base\InvalidConfigException;

/**
 * Базовый класс отправки уведомлений
 *
 * @package user\social\notification
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
abstract class NotificationBase extends Component implements SocNotificationInterface
{
    /**
     * Объект пользователя получателя уведомления
     */
    public User $user;

    /**
     * Текст уведомления
     */
    public string $message;

    /**
     * Отправка запроса
     *
     * @throws InvalidConfigException
     * @throws Exception
     */
    protected function sendRequest(string $url, Client $client, array $params, string $method = 'GET'): Response
    {
        return $client->createRequest()
            ->setMethod($method)
            ->setUrl($url)
            ->setData($params)
            ->send();
    }
}