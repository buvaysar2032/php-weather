<?php

namespace common\modules\user\migrations;

use yii\db\Migration;

/**
 * Class m190919_134429_add_service_data_to_user_ext_table
 */
class m190919_134429_add_service_data_to_user_ext_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    final public function safeUp(): void
    {
        $this->addColumn('{{%user_ext}}', 'service_data', $this->text()->comment('Служебные данные'));
    }

    /**
     * {@inheritdoc}
     */
    final public function safeDown(): void
    {
        $this->dropColumn('{{%user_ext}}', 'service_data');
    }
}
