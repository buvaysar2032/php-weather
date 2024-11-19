<?php

use yii\db\Migration;

/**
 * Handles the creation of table `settings`.
 */
class m130524_201700_create_setting_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    final public function safeUp(): void
    {
        $this->createTable('{{%setting}}', [
            'id' => $this->primaryKey()->comment('ID'),
            'parameter' => $this->string(100)->notNull()->comment('Название параметра'),
            'value' => $this->string(255)->notNull()->comment('Значение'),
            'description' => $this->string(255)->notNull()->comment('Описание параметра'),
        ]);

		// Default Data
        $this->batchInsert('{{%setting}}', ['parameter', 'value', 'description'], [
            ['email_server', 'smtp.test.ru', 'Сервер отправки почты'],
            ['email_port', '25', 'Порт для отправки'],
            ['email_username', 'admin', 'Имя пользователя почтового сервера'],
            ['email_password', 'admin123', 'Пароль от почтового сервера'],
            ['email_from', 'admin@admin.ru', 'Ящик, с которого происходит отправка'],
            ['email_name_from', 'admin@admin.ru', 'Подпись отправителя'],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    final public function safeDown(): void
    {
        $this->dropTable('{{%setting}}');
    }
}
