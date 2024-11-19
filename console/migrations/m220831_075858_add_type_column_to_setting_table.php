<?php

use common\enums\SettingType;
use common\models\Setting;
use yii\db\Migration;

/**
 * Handles adding columns to table `{{%setting}}`.
 */
class m220831_075858_add_type_column_to_setting_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp(): void
    {
        $this->addColumn(
            '{{%setting}}',
            'type',
            $this->string(10)
                ->notNull()
                ->defaultValue(SettingType::String->value)
                ->comment('Тип значения настройки')
        );
        /** @var Setting $setting */
        $setting = Setting::findOne(['parameter' => 'email_password']);
        $setting->type = SettingType::Password->value;
        $setting->save();
        $setting = Setting::findOne(['parameter' => 'email_port']);
        $setting->type = SettingType::Number->value;
        $setting->save();
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown(): void
    {
        $this->dropColumn('{{%setting}}', 'type');
    }
}
