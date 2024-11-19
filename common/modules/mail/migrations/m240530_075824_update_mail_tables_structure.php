<?php

namespace common\modules\mail\migrations;

use yii\db\Migration;

/**
 * Class m240530_075824_update_mail_tables_structure
 */
class m240530_075824_update_mail_tables_structure extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp(): void
    {
        $this->dropForeignKey('{{%fk-mailing_log-mailing_id}}', '{{%mailing_log}}');
        // drops index for column `mailing_id`
        $this->dropIndex('{{%idx-mailing_log-mailing_id}}', '{{%mailing_log}}');
        $this->dropColumn('{{%mailing_log}}', 'mailing_id');
        // drops foreign key for table `{{%mail_template}}`
        $this->dropForeignKey('{{%fk-mailing-mail_template_id}}', '{{%mailing}}');
        // drops index for column `mail_template_id`
        $this->dropIndex('{{%idx-mailing-mail_template_id}}', '{{%mailing}}');
        $this->dropTable('{{%mailing}}');
        $this->dropTable('{{%mail_template}}');
        $this->addColumn('{{%mailing_log}}', 'template', $this->string());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown(): void
    {
        $this->dropColumn('{{%mailing_log}}', 'template');
        $this->createTable('{{%mail_template}}', [
            'id' => $this->primaryKey()->comment('ID'),
            'name' => $this->string(255)->notNull()->comment('Название'),
        ]);
        // Шаблоны по умолчанию
        $this->batchInsert('{{%mail_template}}', ['name'], [
            ['email-confirm'],
            ['passwordResetToken'],
            ['passwordSend']
        ]);
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
        $this->addColumn('{{%mailing_log}}', 'mailing_id', $this->integer(11)->comment('ID рассылки'));
        // creates index for column `mailing_id`
        $this->createIndex(
            '{{%idx-mailing_log-mailing_id}}',
            '{{%mailing_log}}',
            'mailing_id'
        );
        // add foreign key for table `{{%mailing}}`
        $this->addForeignKey(
            '{{%fk-mailing_log-mailing_id}}',
            '{{%mailing_log}}',
            'mailing_id',
            '{{%mailing}}',
            'id',
            'CASCADE'
        );
    }
}
