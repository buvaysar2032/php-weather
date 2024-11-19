<?php

namespace admin\modules\modelExportImport\migrations;

use yii\db\Migration;

/**
 * Class m201224_065743_add_remote_api_url_param
 */
class m201224_065743_add_remote_api_url_param extends Migration
{
    /**
     * {@inheritdoc}
     */
    final public function safeUp(): void
    {
        $this->insert('{{%setting}}', [
            'parameter' => 'remote_import_url',
            'value' => '',
            'description' => 'Ссылка на API удаленного сервера'
        ]);
    }

    /**
     * {@inheritdoc}
     */
    final public function safeDown(): void
    {
        $this->delete('{{%setting}}', ['parameter' => 'remote_import_url']);
    }
}
