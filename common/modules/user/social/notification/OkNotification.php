<?php

namespace common\modules\user\social\notification;

use common\modules\user\social\models\Ok;
use Yii;
use yii\base\InvalidConfigException;
use yii\httpclient\{Client, Exception};

/**
 * Реализация отправки уведомлений в Одноклассники
 *
 * @package user\social\notification
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 *
 * @property-read string $methodUrl
 * @property-read array  $methodParams
 */
final class OkNotification extends NotificationBase
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
        $response = $this->sendRequest($this->methodUrl, $client, $this->methodParams);
        if ($response->data['error_code']) {
            return true;
        }
        return false;
    }

    /**
     * Получение ссылки на метод отправки уведомления.
     */
    public function getMethodUrl(): string
    {
        return 'https://api.ok.ru/fb.do';
    }

    /**
     * Получение массива параметров для уведомления
     */
    public function getMethodParams(): array
    {
        $params = [
            'application_key' => Yii::$app->environment->OK_PUBLIC,
            'format' => 'json',
            'method' => 'notifications.sendSimple',
            'text' => $this->message,
            'uid' => $this->user->getSocialNetworkById(Ok::$soc_name)->user_auth_id,
        ];
        $params['sig'] = $this->getSessionSig($params);
        return $params;
    }

    /**
     * Расчет подписи запроса с secret_key
     */
    private function getSessionSig(array $vars): string
    {
        ksort($vars);
        $params = '';
        foreach ($vars as $key => $value) {
            $params .= "$key=$value";
        }
        return md5($params . Yii::$app->environment->OK_SECRET);
    }

    /**
     * Расчет сигнатуры с MD5(access_token + application_secret_key)
     */
    private function getSig(array $vars, string $access_token, string $secret_key): string
    {
        ksort($vars);
        $params = '';
        foreach ($vars as $key => $value) {
            $params .= $key . '=' . $value;
        }
        return md5($params . md5($access_token . $secret_key));
    }
}