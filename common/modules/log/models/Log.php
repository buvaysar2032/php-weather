<?php

namespace common\modules\log\models;

use admin\models\UserAdmin;
use common\models\AppActiveRecord;
use common\modules\log\{enums\LogOperation, enums\LogStatus, Log as LogModule};
use Yii;
use yii\db\ActiveQuery;

/**
 * This is the model class for table "{{%log}}".
 *
 * @package log
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 *
 * @property int            $id             [int] ID
 * @property string         $table_model    [varchar(255)] Название таблицы
 * @property int            $record_id      [int] ID записи
 * @property string         $field          Название полей
 * @property int            $operation_type [int] Тип операции
 * @property string         $before         Значения до
 * @property string         $after          Значения после
 * @property int            $time           [int] Время
 * @property int            $user_admin_id  [int] ID пользователя
 * @property string         $user_agent     [varchar(255)] User Agent пользователя
 * @property string         $ip             [varchar(16)] IP адрес
 * @property int            $status         [int] Статус операции
 * @property string         $description    [varchar(255)] Примечание
 *
 * @property-read UserAdmin $userAdmin
 */
class Log extends AppActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return '{{%log}}';
    }

    /**
     * Возвращает массив моделей из приложения `common`
     */
    public static function getTableModels(): array
    {
        $tables = [];
        $uniqueTables = self::find()->select(['table_model'])->distinct()->column();
        foreach ($uniqueTables as $table) {
            $tables[$table] = $table;
        }
        return $tables;
    }

    /**
     * {@inheritdoc}
     */
    final public function behaviors(): array
    {
        $behaviors = parent::behaviors();
        unset($behaviors['logger']);
        return $behaviors;
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['table_model', 'field', 'operation_type'], 'required'],
            [['record_id', 'operation_type', 'time', 'user_admin_id', 'status'], 'integer'],
            LogOperation::validator('operation_type'),
            LogStatus::validator('status'),
            [['table_model', 'user_agent', 'description'], 'string', 'max' => 255],
            [['field', 'before', 'after'], 'string'],
            ['ip', 'string', 'max' => 16],
            [
                'user_admin_id',
                'exist',
                'skipOnError' => true,
                'targetClass' => UserAdmin::class,
                'targetAttribute' => ['user_admin_id' => 'id']
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    final public function attributeLabels(): array
    {
        return [
            'id' => 'ID',
            'table_model' => Yii::t(LogModule::MODULE_MESSAGES, 'Table Model'),
            'record_id' => Yii::t(LogModule::MODULE_MESSAGES, 'Record Id'),
            'field' => Yii::t(LogModule::MODULE_MESSAGES, 'Field'),
            'operation_type' => Yii::t(LogModule::MODULE_MESSAGES, 'Operation Type'),
            'before' => Yii::t(LogModule::MODULE_MESSAGES, 'Before'),
            'after' => Yii::t(LogModule::MODULE_MESSAGES, 'After'),
            'time' => Yii::t(LogModule::MODULE_MESSAGES, 'Time'),
            'user_admin_id' => Yii::t(LogModule::MODULE_MESSAGES, 'User Admin Id'),
            'user_agent' => Yii::t(LogModule::MODULE_MESSAGES, 'User Agent'),
            'ip' => 'IP',
            'status' => Yii::t(LogModule::MODULE_MESSAGES, 'Status'),
            'description' => Yii::t(LogModule::MODULE_MESSAGES, 'Description'),
        ];
    }

    final public function getUserAdmin(): ActiveQuery
    {
        return $this->hasOne(UserAdmin::class, ['id' => 'user_admin_id']);
    }
}
