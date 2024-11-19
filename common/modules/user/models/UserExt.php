<?php

namespace common\modules\user\models;

use common\models\AppActiveRecord;
use common\modules\user\Module;
use kartik\validators\PhoneValidator;
use Yii;
use yii\db\ActiveQuery;

/**
 * This is the model class for table "{{%user_ext}}".
 *
 * @package user\models
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 *
 * @property int       $id             [int] ID
 * @property int       $user_id        [int] ID пользователя
 * @property string    $first_name     [varchar(30)] Имя
 * @property string    $middle_name    [varchar(30)] Отчество
 * @property string    $last_name      [varchar(30)] Фамилия
 * @property string    $phone          [varchar(25)] Номер телефона
 * @property int       $rules_accepted [tinyint(1)] Согласие с правилами
 * @property string    $service_data   Служебные данные
 *
 * @property-read User $user
 */
class UserExt extends AppActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return '{{%user_ext}}';
    }

    /**
     * {@inheritdoc}
     */
    final public function rules(): array
    {
        return [
            ['user_id', 'required'],
            ['user_id', 'integer'],
            // name
            [['first_name', 'middle_name', 'last_name',], 'string', 'min' => 1, 'max' => 255],
            ['phone', 'string', 'min' => 10, 'max' => 20],
            ['phone', PhoneValidator::class, 'countryValue' => 'RU'],
            ['rules_accepted', 'boolean'],
            ['service_data', 'string'],
            [
                'user_id',
                'exist',
                'skipOnError' => true,
                'targetClass' => User::class,
                'targetAttribute' => ['user_id' => 'id']
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    final public function attributeLabels(): array
    {
        return [
            'id' => Yii::t(Module::MODULE_MESSAGES, 'ID'),
            'user_id' => Yii::t(Module::MODULE_MESSAGES, 'User ID'),
            'first_name' => Yii::t(Module::MODULE_MESSAGES, 'First Name'),
            'middle_name' => Yii::t(Module::MODULE_MESSAGES, 'Middle Name'),
            'last_name' => Yii::t(Module::MODULE_MESSAGES, 'Last Name'),
            'phone' => Yii::t(Module::MODULE_MESSAGES, 'Phone'),
            'rules_accepted' => Yii::t(Module::MODULE_MESSAGES, 'Rules Accepted'),
            'service_data' => Yii::t(Module::MODULE_MESSAGES, 'Service Data'),
        ];
    }

    final public function getUser(): ActiveQuery
    {
        return $this->hasOne(User::class, ['id' => 'user_id'])->inverseOf('userExt');
    }
}
