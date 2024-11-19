<?php

namespace common\modules\mail\migrations;

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%mailing_log}}`.
 */
class m201001_094311_add_data_column_to_mailing_log_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    final public function safeUp(): void
    {
        $this->addColumn('{{%mailing_log}}', 'data', $this->text()->comment('JSON массив данных в письме'));
        $this->renameColumn('{{%mailing_log}}', 'inited_from', 'app_type');
    }

    /**
     * {@inheritdoc}
     */
    final public function safeDown(): void
    {
        $this->renameColumn('{{%mailing_log}}', 'app_type', 'inited_from');
        $this->dropColumn('{{%mailing_log}}', 'data');
    }
}
