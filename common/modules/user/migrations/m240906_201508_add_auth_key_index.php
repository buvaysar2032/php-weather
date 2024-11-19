<?php

namespace common\modules\user\migrations;

use yii\db\Migration;

/**
 * Class m240906_201508_add_auth_key_index
 */
class m240906_201508_add_auth_key_index extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp(): void
    {
        $this->createIndex('{{%idx-user_agent-auth_key}}', '{{%user_agent}}', 'auth_key');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown(): void
    {
        $this->dropIndex('{{%idx-user_agent-auth_key}}', '{{%user_agent}}');
    }
}
