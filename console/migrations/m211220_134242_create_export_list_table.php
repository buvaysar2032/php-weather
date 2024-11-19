
<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%export_films_list}}`.
 */
class m211220_134242_create_export_list_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp(): void
    {
        $this->createTable('{{%export_list}}', [
            'id' => $this->primaryKey(),
            'filename' => $this->string()->notNull(),
            'date' => $this->integer()->notNull(),
            'count' => $this->integer()->notNull()->defaultValue(0)
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown(): void
    {
        $this->dropTable('{{%export_list}}');
    }
}
