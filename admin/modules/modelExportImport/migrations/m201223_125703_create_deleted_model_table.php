<?php

namespace admin\modules\modelExportImport\migrations;

use yii\db\Migration;

/**
 * Handles the creation of table `{{%deleted_model}}`.
 */
class m201223_125703_create_deleted_model_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    final public function safeUp(): void
    {
        $this->createTable('{{%deleted_model}}', [
            'id' => $this->primaryKey()->comment('ID'),
            'model_class' => $this->string()->notNull()->comment('Класс модели данных'),
            'unique_field' => $this->string()->notNull()->comment('Название поля главного ключа'),
            'deleted_at' => $this->integer()->notNull()->comment('Время удаления'),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    final public function safeDown(): void
    {
        $this->dropTable('{{%deleted_model}}');
    }
}
