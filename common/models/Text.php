<?php

namespace common\models;

use OpenApi\Attributes\{Property, Schema};
use Yii;

/**
 * This is the model class for table "{{%text}}".
 *
 * @package models
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 *
 * @property int    $id    [int] ID
 * @property string $key   [varchar(255)] Ключ текстового поля
 * @property string $value Значение текстового поля
 */
#[Schema(properties: [
    new Property(property: 'key', type: 'string'),
    new Property(property: 'value', type: 'string'),
])]
class Text extends AppActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return '{{%text}}';
    }

    /**
     * Получить текст по ключу
     */
    public static function getValue(string $key): ?string
    {
        $val = self::find()->select(['value'])->where(['key' => $key])->asArray()->one();
        return $val['value'] ?? null;
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['key', 'value'], 'required'],
            ['value', 'string'],
            ['key', 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    final public function attributeLabels(): array
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'key' => Yii::t('app', 'Key'),
            'value' => Yii::t('app', 'Value'),
        ];
    }

    /**
     * {@inheritdoc}
     */
    final public function fields(): array
    {
        return ['key', 'value'];
    }
}
