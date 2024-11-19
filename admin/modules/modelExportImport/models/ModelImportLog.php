<?php

namespace admin\modules\modelExportImport\models;

use admin\modules\modelExportImport\{behaviors\ExportImportBehavior, ModelExportImport};
use common\components\exceptions\ModelSaveException;
use common\models\AppActiveRecord;
use Throwable;
use Yii;
use yii\base\InvalidConfigException;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\StaleObjectException;
use yii\helpers\{ArrayHelper, Json};

/**
 * This is the model class for table "{{%model_import_log}}".
 *
 * @property int                 $id                 [int] ID
 * @property string|ActiveRecord $model_class        [varchar(255)] Класс модели данных
 * @property string              $unique_field       [varchar(255)] Название поля главного ключа
 * @property string              $unique_field_value [varchar(255)] Значение поля главного ключа
 * @property string              $dump_before        Данные до импорта
 * @property string              $dump_after         Данные после импорта
 * @property int                 $imported_at        [int] Время импорта
 *
 * @package modelExportImport\models
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
class ModelImportLog extends AppActiveRecord
{
    /**
     * {@inheritdoc}
     */
    final public function behaviors(): array
    {
        return ArrayHelper::merge(parent::behaviors(), [
            'timestamp' => [
                'class' => TimestampBehavior::class,
                'createdAtAttribute' => 'imported_at',
                'updatedAtAttribute' => false
            ]
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return '{{%model_import_log}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['model_class', 'unique_field', 'unique_field_value', 'dump_before', 'dump_after'], 'required'],
            [['dump_before', 'dump_after'], 'string'],
            ['imported_at', 'integer'],
            [['model_class', 'unique_field', 'unique_field_value'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    final public function attributeLabels(): array
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'model_class' => Yii::t(ModelExportImport::MODULE_MESSAGES, 'Model Class'),
            'unique_field' => Yii::t(ModelExportImport::MODULE_MESSAGES, 'Unique Field'),
            'unique_field_value' => Yii::t(ModelExportImport::MODULE_MESSAGES, 'Unique Field Value'),
            'dump_before' => Yii::t(ModelExportImport::MODULE_MESSAGES, 'Dump Before'),
            'dump_after' => Yii::t(ModelExportImport::MODULE_MESSAGES, 'Dump After'),
            'imported_at' => Yii::t(ModelExportImport::MODULE_MESSAGES, 'Imported At'),
        ];
    }

    /**
     * Добавить запись в лог изменений
     *
     * @throws ModelSaveException
     */
    public static function add(
        string $class,
        string $uniqueField,
        string $uniqueFieldValue,
        array $oldData,
        array $newData
    ): void {
        $log = new self();
        $log->model_class = $class;
        $log->unique_field = $uniqueField;
        $log->unique_field_value = $uniqueFieldValue;
        $log->dump_before = Json::encode($oldData);
        $log->dump_after = Json::encode($newData);
        if (!$log->save()) {
            throw new ModelSaveException($log);
        }
    }

    /**
     * Восстановить состояние модели
     *
     * @throws Throwable
     * @throws ModelSaveException
     * @throws InvalidConfigException
     * @throws StaleObjectException
     */
    final public function reverseModel(): self
    {
        if (!class_exists($this->model_class)) {
            Yii::error('Class ' . $this->model_class . ' does not exists', __METHOD__);
            return $this;
        }
        /** @var yii\db\ActiveRecord|ExportImportBehavior $model */
        $model = $this->model_class::find()->where([$this->unique_field => $this->unique_field_value])->one();
        $model->import(Json::decode($this->dump_before), false);
        return $this;
    }
}
