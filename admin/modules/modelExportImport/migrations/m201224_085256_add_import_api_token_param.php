<?php

namespace admin\modules\modelExportImport\migrations;

use yii\db\Migration;

/**
 * Class m201224_085256_add_import_api_token_param
 */
class m201224_085256_add_import_api_token_param extends Migration
{
    /**
     * {@inheritdoc}
     */
    final public function safeUp(): void
    {
        $this->insert('{{%setting}}', [
            'parameter' => 'import_api_token',
            'value' => '',
            'description' => 'Токен доступа для импорта данных'
        ]);
    }

    /**
     * {@inheritdoc}
     */
    final public function safeDown(): void
    {
        $this->delete('{{%setting}}', ['parameter' => 'import_api_token']);
    }
}
