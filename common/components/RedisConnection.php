<?php

namespace common\components;

use Yii;
use yii\redis\Connection;

class RedisConnection extends Connection
{
    /**
     * {@inheritdoc}
     */
    public function init(): void
    {
        parent::init();
        $this->useSSL = (bool)(int)Yii::$app->environment->REDIS_USE_SSL;
        $this->contextOptions = [
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true,
            ]
        ];
        if (Yii::$app->environment->REDIS_LOCAL_CERT) {
            $this->contextOptions['ssl']['local_cert'] = Yii::$app->environment->REDIS_LOCAL_CERT;
        }
        if (Yii::$app->environment->REDIS_LOCAL_PK) {
            $this->contextOptions['ssl']['local_pk'] = Yii::$app->environment->REDIS_LOCAL_PK;
        }
        if (Yii::$app->environment->REDIS_CAFILE) {
            $this->contextOptions['ssl']['cafile'] = Yii::$app->environment->REDIS_CAFILE;
        }
        $this->scheme = Yii::$app->environment->REDIS_SCHEME ?: 'tcp';
        $this->hostname = Yii::$app->environment->REDIS_HOSTNAME ?: 'localhost';
        $this->port = (int)(Yii::$app->environment->REDIS_PORT ?: 6379);
        $this->database = (int)(Yii::$app->environment->REDIS_DATABASE ?: 0);
        $this->retries = (int)(Yii::$app->environment->REDIS_RETRY ?: 0);
        $this->retryInterval = (int)(Yii::$app->environment->REDIS_RETRIES_INTERVAL ?: 0);
        $this->password = Yii::$app->environment->REDIS_PASSWORD ?: null;
    }
}
