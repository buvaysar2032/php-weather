<?php

namespace common\modules\notification\models;

use common\components\exceptions\ModelSaveException;
use common\enums\Boolean;
use common\models\AppActiveRecord;
use common\modules\notification\enums\Type;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%notification}}".
 *
 * @package notification
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 *
 * @property int         $id
 * @property string      $type       Тип уведомления
 * @property string|null $text       Текст уведомления
 * @property int         $is_viewed  Было ли уведомление просмотрено
 * @property int         $created_at Дата создания
 * @property int         $updated_at Дата изменения
 */
class Notification extends AppActiveRecord
{
    /**
     * {@inheritdoc}
     */
    final public function behaviors(): array
    {
        return ArrayHelper::merge(parent::behaviors(), [
            'timestamp' => [
                'class' => TimestampBehavior::class
            ]
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return '{{%notification}}';
    }

    /**
     * {@inheritdoc}
     */
    final public function rules(): array
    {
        return [
            ['type', 'required'],
            ['text', 'string'],
            [['is_viewed', 'created_at', 'updated_at'], 'integer'],
            ['type', 'string', 'max' => 255],
            Type::validator('type')
        ];
    }

    /**
     * {@inheritdoc}
     */
    final public function attributeLabels(): array
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'type' => Yii::t('app', 'Type'),
            'text' => Yii::t('app', 'Text'),
            'is_viewed' => Yii::t('app', 'Is Viewed'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
        ];
    }

    /**
     * {@inheritdoc}
     */
    final public function fields(): array
    {
        $fields = parent::fields();
        $fields['is_viewed'] = fn () => (bool)$this->is_viewed;
        $fields['text'] = fn () => str_replace("'", '\"', $this->text);
        unset($fields['updated_at']);
        return $fields;
    }

    /**
     * Создать новое уведомление или обновить старое
     *
     * @throws ModelSaveException
     */
    public static function create(Type $type, string $text): ?self
    {
        if (!Yii::$app->getModule('notification')) {
            return null;
        }
        if (!$model = self::find()->where(['type' => $type->value, 'text' => $text])->one()) {
            $model = new self();
            $model->type = $type->value;
            $model->text = $text;
        }
        $model->is_viewed = Boolean::No->value;
        $model->updated_at = time();
        if (!$model->save()) {
            throw new ModelSaveException($model);
        }
        return $model;
    }
}
