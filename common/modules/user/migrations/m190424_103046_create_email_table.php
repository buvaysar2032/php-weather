<?php

namespace common\modules\user\migrations;

use yii\db\Migration;

/**
 * Handles the creation of table `{{%email}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%user}}`
 */
class m190424_103046_create_email_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    final public function safeUp(): void
    {
        $this->createTable('{{%email}}', [
            'id' => $this->primaryKey()->comment('ID'),
            'user_id' => $this->integer()->notNull()->comment('ID пользователя'),
            'value' => $this->string()->notNull()->comment('Значение'),
            'confirm_token' => $this->string()->comment('Токен подтверждения'),
            'is_confirmed' => $this->boolean()->notNull()->defaultValue(0)->comment('Адрес подтвержден'),
        ]);

        // creates index for column `user_id`
        $this->createIndex(
            '{{%idx-email-user_id}}',
            '{{%email}}',
            'user_id'
        );

        // add foreign key for table `{{%user}}`
        $this->addForeignKey(
            '{{%fk-email-user_id}}',
            '{{%email}}',
            'user_id',
            '{{%user}}',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    final public function safeDown(): void
    {
        // drops foreign key for table `{{%user}}`
        $this->dropForeignKey(
            '{{%fk-email-user_id}}',
            '{{%email}}'
        );

        // drops index for column `user_id`
        $this->dropIndex(
            '{{%idx-email-user_id}}',
            '{{%email}}'
        );

        $this->dropTable('{{%email}}');
    }
}
