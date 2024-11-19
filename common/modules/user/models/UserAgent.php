<?php

namespace common\modules\user\models;

use common\models\AppActiveRecord;
use common\modules\user\Module;
use Yii;
use yii\db\ActiveQuery;

/**
 * This is the model class for table "{{%user_agent}}".
 *
 * @package user\models
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 *
 * @property int       $id       [int] ID
 * @property int       $user_id  [int] ID пользователя
 * @property string    $value    [varchar(255)] Значение
 * @property string    $auth_key [varchar(255)] Ключ авторизации
 *
 * @property-read User $user
 */
class UserAgent extends AppActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return '{{%user_agent}}';
    }

    public static function getAuthKey(int $userId): ?string
    {
        $userAgent = self::find()
            ->select(['auth_key'])
            ->where(['user_id' => $userId, 'value' => Yii::$app->request->shortUserAgent])
            ->asArray()
            ->one();
        return $userAgent['auth_key'] ?? null;
    }

    /**
     * {@inheritdoc}
     */
    final public function rules(): array
    {
        return [
            [['user_id', 'value', 'auth_key'], 'required'],
            ['user_id', 'integer'],
            [['value', 'auth_key'], 'string', 'max' => 255],
            [
                'user_id',
                'exist',
                'skipOnError' => true,
                'targetClass' => User::class,
                'targetAttribute' => ['user_id' => 'id']
            ]
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
            'value' => Yii::t(Module::MODULE_MESSAGES, 'User Agent'),
            'auth_key' => Yii::t(Module::MODULE_MESSAGES, 'Auth Key'),
        ];
    }

    final public function getUser(): ActiveQuery
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }
}
