<?php

namespace common\components;

use PDO;
use Yii;
use yii\db\Connection;

/**
 * Класс DbConnection
 *
 * Переопределяет свою конфигурацию переменными окружения:
 *  - `DB_HOST` - имя хоста, по умолчанию "localhost"
 *  - `DB_NAME` - название БД
 *  - `DB_USER` - логин
 *  - `DB_PASS` - Пароль
 *  - `DB_CHARSET` - кодировка подключения, по умолчанию "utf8mb4"
 *  - `DB_SLAVE_HOSTS` - имя хостов всех slave-ов
 *  - `DB_SLAVE_NAME` - название БД, по умолчанию DB_NAME
 *  - `DB_SLAVE_USER` - логин slave, по умолчанию DB_USER
 *  - `DB_SLAVE_PASS` - Пароль slave, по умолчанию DB_PASS
 *
 * @package connection\components
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
class DbConnection extends Connection
{
    public $enableSchemaCache = YII_ENV_PROD;

    public $schemaCacheDuration = 120;

    /**
     * {@inheritdoc}
     */
    public function init(): void
    {
        parent::init();
        $dbname = Yii::$app->environment->DB_NAME;
        $host = Yii::$app->environment->DB_HOST ?: 'localhost';
        $this->dsn = "mysql:host=$host;dbname=$dbname";
        $this->username = Yii::$app->environment->DB_USER;
        $this->password = Yii::$app->environment->DB_PASS;
        $this->charset = Yii::$app->environment->DB_CHARSET ?: 'utf8mb4';
        if (Yii::$app->environment->DB_SLAVE_HOSTS) {
            $this->slaveConfig = [
                'class' => Connection::class,
                'username' => Yii::$app->environment->DB_SLAVE_USER ?: Yii::$app->environment->DB_USER,
                'password' => Yii::$app->environment->DB_SLAVE_PASS ?: Yii::$app->environment->DB_PASS,
                'attributes' => [PDO::ATTR_TIMEOUT => 10]
            ];
            $dbname = Yii::$app->environment->DB_SLAVE_NAME ?: Yii::$app->environment->DB_NAME;
            $hosts = explode(',', Yii::$app->environment->DB_SLAVE_HOSTS);
            foreach ($hosts as $host) {
                if ($host = trim($host)) {
                    $this->slaves = [
                        ['dsn' => "mysql:host=$host;dbname=$dbname"]
                    ];
                }
            }
        }
    }
}
