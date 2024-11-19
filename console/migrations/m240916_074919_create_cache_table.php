<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%cache}}`.
 */
class m240916_074919_create_cache_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    final public function safeUp(): void
    {
        $this->createTable('{{%cache}}', [
            'id' => $this->string(128)->notNull(),
            'expire' => $this->integer(11),
            'data' => $this->binary(),
        ]);
        $this->addPrimaryKey('cache_pk', '{{%cache}}', 'id');
        $this->createIndex('{{%idx-cache-expire}}', '{{%cache}}', 'expire');
    }

    /**
     * {@inheritdoc}
     */
    final public function safeDown(): void
    {
        $this->dropIndex('{{%idx-cache-expire}}', '{{%cache}}');
        $this->dropTable('{{%cache}}');
    }
}
