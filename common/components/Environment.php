<?php

namespace common\components;

use yii\base\Component;

/**
 * Environment configuration component
 *
 * @property string|null $APP_DOMAIN
 * @property string|null $BASE_URI
 * @property string|null $TRUSTED_HOSTS
 * @property string|null $CORS_DOMAINS
 * @property string|null $CSP_NONCE
 *
 * @property string|null $DB_NAME
 * @property string|null $DB_HOST
 * @property string|null $DB_USER
 * @property string|null $DB_PASS
 * @property string|null $DB_CHARSET
 * @property string|null $DB_SLAVE_HOSTS
 * @property string|null $DB_SLAVE_NAME
 * @property string|null $DB_SLAVE_USER
 * @property string|null $DB_SLAVE_PASS
 *
 * @property string|null $RECAPTCHA_V2_KEY
 * @property string|null $RECAPTCHA_V2_SECRET
 * @property string|null $RECAPTCHA_V3_KEY
 * @property string|null $RECAPTCHA_V3_SECRET
 *
 * @property string|null $VK_CLIENT   ID зарегистрированного приложения ВКонтакте
 * @property string|null $VK_SECRET   Секретный ключ зарегистрированного приложения ВКонтакте
 * @property string|null $VK_SERVICE  Сервисный ключ доступа зарегистрированного приложения ВКонтакте
 * @property string|null $FB_CLIENT   ID зарегистрированного приложения Facebook
 * @property string|null $FB_SECRET   Секретный ключ зарегистрированного приложения Facebook
 * @property string|null $OK_CLIENT   ID зарегистрированного приложения Одноклассники
 * @property string|null $OK_SECRET   Секретный ключ зарегистрированного приложения Одноклассники
 * @property string|null $OK_PUBLIC   Публичный ключ зарегистрированного приложения Одноклассники
 * @property string|null $GGL_CLIENT  ID зарегистрированного в Google приложения
 * @property string|null $GGL_SECRET  Секретный ключ зарегистрированного в Google приложения
 * @property string|null $YAID_CLIENT ID зарегистрированного в Яндекс ID приложения
 * @property string|null $YAID_SECRET Секретный ключ зарегистрированного в Яндекс ID приложения
 *
 * @property string|null $MEMCACHED_HOST
 * @property string|null $MEMCACHED_PORT
 *
 * @property string|null $REDIS_SCHEME
 * @property string|null $REDIS_HOSTNAME
 * @property string|null $REDIS_PORT
 * @property string|null $REDIS_DATABASE
 * @property string|null $REDIS_RETRY
 * @property string|null $REDIS_RETRIES_INTERVAL
 * @property string|null $REDIS_PASSWORD
 * @property string|null $REDIS_USE_SSL
 * @property string|null $REDIS_LOCAL_CERT
 * @property string|null $REDIS_LOCAL_PK
 * @property string|null $REDIS_CAFILE
 *
 * @property string|null $S3_ENDPOINT
 * @property string|null $S3_REGION
 * @property string|null $S3_KEY
 * @property string|null $S3_SECRET
 * @property string|null $S3_BUCKET
 * @property string|null $S3_BASEURL
 * @property string|null $S3_PRIVATE_BUCKET
 *
 * @property string|null $EMAIL_HOST
 * @property string|null $EMAIL_PORT
 * @property string|null $EMAIL_USERNAME
 * @property string|null $EMAIL_PASSWORD
 * @property string|null $EMAIL_FROM
 * @property string|null $EMAIL_NAME_FROM
 */
class Environment extends Component
{
    /**
     * {@inheritdoc}
     */
    public function __get($name)
    {
        if (strtoupper($name) === $name) {
            return self::readEnv($name);
        }
        return parent::__get($name);
    }

    /**
     * {@inheritdoc}
     */
    public function __set($name, $value): void
    {
        if (strtoupper($name) === $name) {
            $_ENV[$name] = $value;
            return;
        }
        parent::__set($name, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function __isset($name): bool
    {
        if (strtoupper($name) === $name) {
            return array_key_exists($name, $_ENV) || !empty(getenv($name));
        }
        return parent::__isset($name);
    }

    /**
     * {@inheritdoc}
     */
    public function __unset($name)
    {
        if (array_key_exists($name, $_ENV) && strtoupper($name) === $name) {
            unset($_ENV[$name]);
            return;
        }
        parent::__unset($name);
    }

    /**
     * {@inheritdoc}
     */
    public function hasProperty($name, $checkVars = true, $checkBehaviors = true): bool
    {
        return strtoupper($name) === $name || parent::hasProperty($name, $checkVars, $checkBehaviors);
    }

    /**
     * {@inheritdoc}
     */
    public function canGetProperty($name, $checkVars = true, $checkBehaviors = true): bool
    {
        if (strtoupper($name) === $name) {
            return array_key_exists($name, $_ENV) || getenv($name);
        }
        return parent::canGetProperty($name, $checkVars, $checkBehaviors);
    }

    /**
     * {@inheritdoc}
     */
    public function canSetProperty($name, $checkVars = true, $checkBehaviors = true): bool
    {
        return strtoupper($name) === $name || parent::canSetProperty($name, $checkVars, $checkBehaviors);
    }

    /**
     * Получить значение переменной окружения
     */
    public static function readEnv(string $name): ?string
    {
        $value = $_ENV[$name] ?? (!in_array(getenv($name), [false, ''], true) ? getenv($name) : null);
        if (is_string($value)) {
            $value = trim($value);
        }
        return $value;
    }
}
