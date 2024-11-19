<?php

namespace common\modules\mail\migrations;

use yii\db\Migration;

/**
 * Handles the creation of table `{{%mailing_log}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%mailing}}`
 * - `{{%user}}`
 */
class m190422_114622_create_mailing_log_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    final public function safeUp(): void
    {
        $this->createTable('{{%mailing_log}}', [
            'id' => $this->primaryKey()->comment('ID'),
            'mailing_id' => $this->integer(11)->comment('ID рассылки'),
            'mailing_subject' => $this->string(255)->comment('Тема'),
            'mail_to' => $this->string(128)->comment('Получатель'),
            'user_id' => $this->integer(11)->comment('ID пользователя'),
            'date' => $this->integer(11)->comment('Дата отправки'),
            'status' => $this->integer(11)->comment('Статус отправки'),
            'description' => $this->text()->comment('Описание'),
            'inited_from' => $this->integer(1)->notNull()->defaultValue(0)->comment('Инициатор отправки'),
            'mailing_log_id' => $this->integer(11)->comment('ID родительского лога'),
        ]);

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

        // creates index for column `user_id`
        $this->createIndex(
            '{{%idx-mailing_log-user_id}}',
            '{{%mailing_log}}',
            'user_id'
        );

        // add foreign key for table `{{%user}}`
        $this->addForeignKey(
            '{{%fk-mailing_log-user_id}}',
            '{{%mailing_log}}',
            'user_id',
            '{{%user}}',
            'id',
            'CASCADE'
        );

        // creates index for column `log_id`
        $this->createIndex(
            '{{%idx-mailing_log-mailing_log_id}}',
            '{{%mailing_log}}',
            'mailing_log_id'
        );

        // add foreign key for table `{{%log}}`
        $this->addForeignKey(
            '{{%fk-mailing_log-mailing_log_id}}',
            '{{%mailing_log}}',
            'mailing_log_id',
            '{{%mailing_log}}',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    final public function safeDown(): void
    {
        // drops foreign key for table `{{%mailing}}`
        $this->dropForeignKey(
            '{{%fk-mailing_log-mailing_id}}',
            '{{%mailing_log}}'
        );

        // drops index for column `mailing_id`
        $this->dropIndex(
            '{{%idx-mailing_log-mailing_id}}',
            '{{%mailing_log}}'
        );

        // drops foreign key for table `{{%user}}`
        $this->dropForeignKey(
            '{{%fk-mailing_log-user_id}}',
            '{{%mailing_log}}'
        );

        // drops index for column `user_id`
        $this->dropIndex(
            '{{%idx-mailing_log-user_id}}',
            '{{%mailing_log}}'
        );

        // drops foreign key for table `{{%user}}`
        $this->dropForeignKey(
            '{{%fk-mailing_log-mailing_log_id}}',
            '{{%mailing_log}}'
        );

        // drops index for column `user_id`
        $this->dropIndex(
            '{{%idx-mailing_log-mailing_log_id}}',
            '{{%mailing_log}}'
        );

        $this->dropTable('{{%mailing_log}}');
    }
}
