<?php

namespace common\modules\log\migrations;

use yii\base\NotSupportedException;
use yii\db\Migration;

/**
 * Handles the creation of table `{{%log}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%user_admin}}`
 */
class m190418_082937_create_log_table extends Migration
{
    /**
     * {@inheritdoc}
     * @throws NotSupportedException
     */
    final public function safeUp(): void
    {
        $this->createTable('{{%log}}', [
            'id' => $this->primaryKey()->comment('ID'),
            'table_model' => $this->string(255)->notNull()->comment('Название таблицы'),
            'record_id' => $this->integer(11)->comment('ID записи'),
            'field' => $this->text()->notNull()->comment('Название полей'),
            'operation_type' => $this->integer(11)->notNull()->comment('Тип операции'),
            'before' => $this->getDb()->getSchema()->createColumnSchemaBuilder('longtext')->comment('Значения до'),
            'after' => $this->getDb()->getSchema()->createColumnSchemaBuilder('longtext')->comment('Значения после'),
            'time' => $this->integer(11)->comment('Время'),
            'user_admin_id' => $this->integer(11)->comment('ID пользователя'),
            'user_agent' => $this->string(255)->comment('User Agent пользователя'),
            'ip' => $this->string(16)->comment('IP адрес'),
            'status' => $this->integer(11)->comment('Статус операции'),
            'description' => $this->string(255)->defaultValue(null)->comment('Примечание'),
        ]);

        // creates index for column `user_admin_id`
        $this->createIndex(
            '{{%idx-log-user_admin_id}}',
            '{{%log}}',
            'user_admin_id'
        );

        // add foreign key for table `{{%user_admin}}`
        $this->addForeignKey(
            '{{%fk-log-user_admin_id}}',
            '{{%log}}',
            'user_admin_id',
            '{{%user_admin}}',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    final public function safeDown(): void
    {
        // drops foreign key for table `{{%user_admin}}`
        $this->dropForeignKey(
            '{{%fk-log-user_admin_id}}',
            '{{%log}}'
        );

        // drops index for column `user_admin_id`
        $this->dropIndex(
            '{{%idx-log-user_admin_id}}',
            '{{%log}}'
        );

        $this->dropTable('{{%log}}');
    }
}
