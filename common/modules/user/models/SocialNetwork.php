<?php

namespace common\modules\user\models;

use common\models\AppActiveRecord;
use common\modules\user\Module;
use Yii;
use yii\db\ActiveQuery;

/**
 * This is the model class for table "{{%social_network}}".
 *
 * @package user\models
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 *
 * @property int       $id                [int] ID
 * @property int       $user_id           [int] ID пользователя
 * @property string    $social_network_id [varchar(10)] ID/тип соц сети
 * @property string    $user_auth_id      [varchar(300)] ID пользователя в соц. сети
 * @property string    $access_token      [varchar(300)] Токен доступа
 * @property int       $last_auth_date    [int] Дата последней авторизации
 *
 * @property-read User $user
 */
class SocialNetwork extends AppActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return '{{%social_network}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['user_id', 'social_network_id', 'user_auth_id'], 'required'],
            ['user_id', 'integer'],
            ['last_auth_date', 'safe'],
            [['social_network_id', 'user_auth_id'], 'string', 'max' => 255],
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
            'social_network_id' => Yii::t(Module::MODULE_MESSAGES, 'Social Network ID'),
            'user_auth_id' => Yii::t(Module::MODULE_MESSAGES, 'User Auth ID'),
            'access_token' => Yii::t(Module::MODULE_MESSAGES, 'Access Token'),
            'last_auth_date' => Yii::t(Module::MODULE_MESSAGES, 'Last Auth Date'),
        ];
    }

    final public function getUser(): ActiveQuery
    {
        return $this->hasOne(User::class, ['id' => 'user_id'])->inverseOf('socialNetwork');
    }
}
