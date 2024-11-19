<?php

namespace common\components;

use Yii;
use yii\helpers\StringHelper;
use yii\web\Request as YiiRequest;

/**
 * Class Request
 *
 * @package common\components
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 * @author  d.potehin <d.potehin@peppers-studio.ru>
 *
 * @property-read null|int    $longUserIp
 * @property-read null|string $shortUserAgent
 * @property-read null|string $fingerprint
 */
class Request extends YiiRequest
{
    public $secureProtocolHeaders = [
        'X-Forwarded-Proto' => ['https'],
        'X-Scheme' => ['https'],
        'Front-End-Https' => ['on'],
    ];

    /**
     * {@inheritdoc}
     */
    final public function init(): void
    {
        parent::init();
        if (Yii::$app->environment->TRUSTED_HOSTS) {
            $this->trustedHosts = explode(',', Yii::$app->environment->TRUSTED_HOSTS);
        } else {
            // Если разрешены все хосты, то разрешаем secureProtocolHeaders
            $this->secureHeaders = array_diff($this->secureHeaders, array_keys($this->secureProtocolHeaders));
        }
    }

    /**
     * Получить параметр из запроса, если не найден в `$_POST`, то ищет в `$_GET`
     *
     * @param string $paramName Имя параметра
     *
     * @return mixed Значение параметра или null, если его нет
     */
    final public function getParameter(string $paramName): mixed
    {
        return $this->post($paramName) ?? $this->get($paramName);
    }

    final public function getHostInfo(): ?string
    {
        if (!empty(Yii::$app->environment->APP_DOMAIN)) {
            $host = Yii::$app->environment->APP_DOMAIN;
            if (!preg_match('#^https?://#', $host)) {
                $host = ($this->isSecureConnection ? 'https' : 'http') . '://' . $host;
            }
            return $host;
        }
        return parent::getHostInfo();
    }

    /**
     * {@inheritdoc}
     */
    final public function getIsSecureConnection(): bool
    {
        $isSecure = parent::getIsSecureConnection();
        if (!$isSecure && (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] === 443)) {
            $isSecure = true;
        }
        return $isSecure;
    }

    /**
     * IP адрес пользователя в long формате
     *
     * @see self::getUserIP()
     */
    final public function getLongUserIp(): ?int
    {
        $ip = $this->getUserIP();
        if ($ip) {
            $ip = ip2long($ip);
        }
        return $ip;
    }

    /**
     * {@inheritdoc}
     */
    final public function getUserAgent(): ?string
    {
        $user_agent = strip_tags(parent::getUserAgent());
        if (YII_ENV_TEST || empty($user_agent)) {
            $user_agent = 'Codeception_PHPUnit';
        } elseif (
            YII_ENV_DEV &&
            Yii::$app->request->headers->has('dev-user-agent') &&
            Yii::$app->request->headers->get('dev-user-agent')
        ) {
            $user_agent = 'Developer_User_Agent';
        }
        return $user_agent;
    }

    /**
     * User-Agent пользователя в сокращенном виде
     *
     * @see self::getUserAgent()
     */
    final public function getShortUserAgent(): ?string
    {
        return StringHelper::truncate(preg_replace('#(\D*)/[\d.]*#', '$1', $this->userAgent), 252);
    }

    final public function getFingerprint(): ?string
    {
        return $this->getUserIp() . '__' . $this->getUserAgent();
    }
}
