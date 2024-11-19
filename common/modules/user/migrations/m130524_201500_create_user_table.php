<?php

namespace common\modules\user\migrations;

use common\modules\user\enums\Status;
use yii\db\Migration;

/**
 * Handles the creation of table `user`.
 */
class m130524_201500_create_user_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    final public function safeUp(): void
    {
        $this->createTable('{{%user}}', [
            'id' => $this->primaryKey()->comment('ID'),
            'username' => $this->string()->comment('Никнейм'),
            'password_hash' => $this->string(60)->comment('Хеш пароля'),
            'auth_source' => $this->string()->comment('Источник авторизации'),
            'auth_key' => $this->string()->comment('Ключ авторизации'),
            'password_reset_token' => $this->string(50)->unique()->comment('Токен сброса пароля'),
            'last_login_at' => $this->integer()->comment('Дата последней авторизации'),
            'created_at' => $this->integer()->notNull()->comment('Дата создания'),
            'updated_at' => $this->integer()->notNull()->comment('Дата изменения'),
            'status' => $this->integer()->notNull()->defaultValue(Status::Active->value)->comment('Статус'),
            'last_ip' => $this->bigInteger()->comment('Последний IP адрес'),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    final public function safeDown(): void
    {
        $this->dropTable('{{%user}}');
    }
}
