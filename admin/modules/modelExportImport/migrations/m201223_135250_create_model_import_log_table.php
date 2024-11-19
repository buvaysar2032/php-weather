<?php

namespace admin\modules\modelExportImport\migrations;

use yii\base\Exception;
use yii\db\Migration;

/**
 * Handles the creation of table `{{%model_import_log}}`.
 */
class m201223_135250_create_model_import_log_table extends Migration
{
    /**
     * {@inheritdoc}
     * @throws Exception
     */
    public function safeUp(): void
    {
        $this->createTable('{{%model_import_log}}', [
            'id' => $this->primaryKey()->comment('ID'),
            'model_class' => $this->string()->notNull()->comment('Класс модели данных'),
            'unique_field' => $this->string()->notNull()->comment('Название поля главного ключа'),
            'unique_field_value' => $this->string()->notNull()->comment('Значение поля главного ключа'),
            'dump_before' => $this->getDb()->getSchema()->createColumnSchemaBuilder('longtext')->notNull()->comment('Данные до импорта'),
            'dump_after' => $this->getDb()->getSchema()->createColumnSchemaBuilder('longtext')->notNull()->comment('Данные после импорта'),
            'imported_at' => $this->integer()->notNull()->comment('Время импорта'),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    final public function safeDown(): void
    {
        $this->dropTable('{{%model_import_log}}');
    }
}
