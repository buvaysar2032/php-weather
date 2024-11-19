<?php

use yii\db\Migration;

/**
 * Handles the creation of table `text`.
 */
class m130524_201800_create_text_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    final public function safeUp(): void
    {
        $this->createTable('{{%text}}', [
            'id' => $this->primaryKey()->comment('ID'),
            'key' => $this->string()->notNull()->comment('Ключ текстового поля'),
            'value' => $this->text()->notNull()->comment('Значение текстового поля'),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    final public function safeDown(): void
    {
        $this->dropTable('{{%text}}');
    }
}
