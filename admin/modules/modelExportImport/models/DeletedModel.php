<?php

namespace admin\modules\modelExportImport\models;

use common\models\AppActiveRecord;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%deleted_model}}".
 *
 * @property int                 $id           [int] ID
 * @property ActiveRecord|string $model_class  [varchar(255)] Класс модели данных
 * @property string              $unique_field [varchar(255)] Название поля главного ключа
 * @property int                 $deleted_at   [int] Время удаления
 *
 * @package modelExportImport\models
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
class DeletedModel extends AppActiveRecord
{
    /**
     * {@inheritdoc}
     */
    final public function behaviors(): array
    {
        return ArrayHelper::merge(parent::behaviors(), [
            'timestamp' => [
                'class' => TimestampBehavior::class,
                'createdAtAttribute' => 'deleted_at',
                'updatedAtAttribute' => false
            ]
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return '{{%deleted_model}}';
    }

    /**
     * {@inheritdoc}
     */
    final public function rules(): array
    {
        return [
            [['model_class', 'unique_field'], 'required'],
            ['deleted_at', 'integer'],
            [['model_class', 'unique_field'], 'string', 'max' => 255]
        ];
    }

    /**
     * {@inheritdoc}
     */
    final public function attributeLabels(): array
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'model_class' => Yii::t('app', 'Model Class'),
            'unique_field' => Yii::t('app', 'Unique Field'),
            'deleted_at' => Yii::t('app', 'Deleted At')
        ];
    }
}
