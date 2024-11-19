<?php

namespace common\modules\user\migrations;

use yii\db\Migration;

/**
 * Handles the creation of table `{{%user_agent}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%user}}`
 */
class m191115_085728_create_user_agent_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    final public function safeUp(): void
    {
        $this->createTable('{{%user_agent}}', [
            'id' => $this->primaryKey()->comment('ID'),
            'user_id' => $this->integer(11)->notNull()->comment('ID пользователя'),
            'value' => $this->string(255)->notNull()->comment('Значение'),
            'auth_key' => $this->string(255)->notNull()->comment('Ключ авторизации'),
        ]);
        $this->dropColumn('{{%user}}', 'auth_key');

        // creates index for column `user_id`
        $this->createIndex('{{%idx-user_agent-user_id}}', '{{%user_agent}}', 'user_id');

        // add foreign key for table `{{%user}}`
        $this->addForeignKey(
            '{{%fk-user_agent-user_id}}',
            '{{%user_agent}}',
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
        $this->dropForeignKey('{{%fk-user_agent-user_id}}', '{{%user_agent}}');

        // drops index for column `user_id`
        $this->dropIndex('{{%idx-user_agent-user_id}}', '{{%user_agent}}');

        $this->dropTable('{{%user_agent}}');

        $this->addColumn('{{%user}}', 'auth_key', $this->string());
    }
}
