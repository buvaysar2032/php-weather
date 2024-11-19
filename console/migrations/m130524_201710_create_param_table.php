<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%params}}`.
 */
class m130524_201710_create_param_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    final public function safeUp(): void
    {
        $this->createTable('{{%param}}', [
            'id' => $this->primaryKey()->comment('ID'),
            'group' => $this->string()->comment('Группа параметров'),
            'key' => $this->string()->notNull()->comment('Название параметра'),
            'value' => $this->string()->comment('Значение'),
            'description' => $this->string(255)->comment('Описание параметра'),
            'deletable' => $this->boolean()->defaultValue(true)->comment('Разрешено ли удалять параметр из панели администратора'),
            'is_active' => $this->boolean()->defaultValue(true)->comment('Доступен ли параметр во внешнем API'),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    final public function safeDown(): void
    {
        $this->dropTable('{{%param}}');
    }
}
