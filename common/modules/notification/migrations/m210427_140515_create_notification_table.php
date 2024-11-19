<?php

namespace common\modules\notification\migrations;

use yii\db\Migration;

/**
 * Handles the creation of table `{{%notification}}`.
 *
 * @package common\modules\notification\migrations
 * @author m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
class m210427_140515_create_notification_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    final public function safeUp(): void
    {
        $this->createTable('{{%notification}}', [
            'id' => $this->primaryKey(),
            'type' => $this->string()->notNull()->comment('Тип уведомления'),
            'text' => $this->text()->comment('Текст уведомления'),
            'is_viewed' => $this->boolean()->notNull()->defaultValue(0)->comment('Было ли уведомление просмотрено'),
            'created_at' => $this->integer()->notNull()->comment('Дата создания'),
            'updated_at' => $this->integer()->notNull()->comment('Дата изменения'),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    final public function safeDown(): void
    {
        $this->dropTable('{{%notification}}');
    }
}
