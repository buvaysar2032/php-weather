<?php

use common\enums\ParamType;
use yii\db\Migration;

/**
 * Handles adding columns to table `{{%param}}`.
 */
class m210401_150836_add_type_column_to_param_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    final public function safeUp(): void
    {
        $this->addColumn(
            '{{%param}}',
            'type', $this->string(10)->notNull()
                ->defaultValue(ParamType::Text->value)
                ->comment('Тип значения параметра')
        );
    }

    /**
     * {@inheritdoc}
     */
    final public function safeDown(): void
    {
        $this->dropColumn('{{%param}}', 'type');
    }
}
