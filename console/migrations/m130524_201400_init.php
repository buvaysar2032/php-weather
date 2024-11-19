<?php

use admin\enums\AdminStatus;
use yii\db\Migration;

/**
 * Class m130524_201400_init
 */
class m130524_201400_init extends Migration
{
    /**
     * {@inheritdoc}
     */
    final public function up(): void
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%user_admin}}', [
            'id' => $this->primaryKey()->comment('ID'),
            'username' => $this->string(150)->notNull()->unique()->comment('Имя пользователя'),
            'auth_key' => $this->string(32)->notNull()->comment('Ключ авторизации'),
            'password_hash' => $this->string(60)->notNull()->comment('Хеш пароля'),
            'password_reset_token' => $this->string(50)->unique()->comment('Токен сброса пароля'),
            'email' => $this->string(150)->notNull()->unique()->comment('Email адрес'),
            'status' => $this->smallInteger()->notNull()->defaultValue(AdminStatus::Active->value)->comment('Статус'),
            'created_at' => $this->integer()->notNull()->comment('Дата создания'),
            'updated_at' => $this->integer()->notNull()->comment('Дата обновления'),
        ], $tableOptions);
    }

    /**
     * {@inheritdoc}
     */
    final public function down(): void
    {
        $this->dropTable('{{%user_admin}}');
    }
}
