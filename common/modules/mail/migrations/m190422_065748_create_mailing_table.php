<?php

namespace common\modules\mail\migrations;

use yii\db\Migration;

/**
 * Handles the creation of table `{{%mailing}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%mail_template}}`
 */
class m190422_065748_create_mailing_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    final public function safeUp(): void
    {
        $this->createTable('{{%mailing}}', [
            'id' => $this->primaryKey()->comment('ID'),
            'name' => $this->string(255)->notNull()->comment('Название'),
            'mailing_type' => $this->integer(11)->notNull()->comment('Типа рассылки'),
            'mail_template_id' => $this->integer(11)->notNull()->comment('ID шаблона'),
            'mail_subject' => $this->string(255)->notNull()->comment('Тема рассылки'),
            'comment' => $this->string(255)->comment('Комментарий'),
        ]);

        // creates index for column `mail_template_id`
        $this->createIndex('{{%idx-mailing-mail_template_id}}', '{{%mailing}}', 'mail_template_id');

        // add foreign key for table `{{%mail_template}}`
        $this->addForeignKey(
            '{{%fk-mailing-mail_template_id}}',
            '{{%mailing}}',
            'mail_template_id',
            '{{%mail_template}}',
            'id',
            'CASCADE'
        );

        $this->insert('{{%mailing}}', [
            'name' => 'email-confirm',
            'mailing_type' => 1,
            'mail_template_id' => 1,
            'mail_subject' => 'Подтверждение почты',
            'comment' => 'Рассылка писем подтверждения',
        ]);
        $this->insert('{{%mailing}}', [
            'name' => 'passwordResetToken',
            'mailing_type' => 1,
            'mail_template_id' => 2,
            'mail_subject' => 'Сброс пароля',
            'comment' => 'Сброс пароля пользователя',
        ]);
        $this->insert('{{%mailing}}', [
            'name' => 'passwordSend',
            'mailing_type' => 1,
            'mail_template_id' => 3,
            'mail_subject' => 'Новый пароль',
            'comment' => 'Отправка нового пароля пользователю',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    final public function safeDown(): void
    {
        // drops foreign key for table `{{%mail_template}}`
        $this->dropForeignKey('{{%fk-mailing-mail_template_id}}', '{{%mailing}}');

        // drops index for column `mail_template_id`
        $this->dropIndex('{{%idx-mailing-mail_template_id}}', '{{%mailing}}');

        $this->dropTable('{{%mailing}}');
    }
}
