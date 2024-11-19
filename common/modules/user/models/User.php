<?php

namespace common\modules\user\models;

use common\components\export\ExportConfig;
use common\models\AppActiveRecord;
use common\modules\user\{enums\Status, Module};
use OpenApi\Attributes\{Property, Schema};
use Yii;
use yii\base\Exception;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\helpers\ArrayHelper;
use yii\web\IdentityInterface;

/**
 * This is the model class for table "{{%user}}".
 *
 * @package user\models
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 *
 * @property int                  $id                   [int] ID
 * @property string               $username             [varchar(255)] Никнейм
 * @property string               $password_hash        [varchar(60)] Хеш пароля
 * @property string               $auth_source          [varchar(255)] Источник авторизации
 * @property string               $password_reset_token [varchar(255)] Токен сброса пароля
 * @property int                  $last_login_at        [int] Дата последней авторизации
 * @property int                  $created_at           [int] Дата создания
 * @property int                  $updated_at           [int] Дата изменения
 * @property int                  $status               [int] Статус
 * @property int                  $last_ip              [bigint] Последний IP адрес
 *
 * @property-read array           $profile
 *
 * @property-read UserExt         $userExt
 * @property-read Email|null      $email
 * @property-read null|string     $authKey
 * @property-read SocialNetwork[] $socialNetworks
 *
 * @property-write string         $password
 */
class User extends AppActiveRecord implements IdentityInterface, ExportConfig
{
    use Identity;
    use ResettablePassword;

    /**
     * Источник авторизации по умолчанию
     */
    public const AUTH_SOURCE_EMAIL = 'e-mail';

    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return '{{%user}}';
    }

    /**
     * Get user profile
     */
    #[Schema(schema: 'Profile', properties: [
        new Property(property: 'id', description: 'User ID', type: 'integer', example: 1),
        new Property(property: 'access_token', description: 'Bearer Токен доступа к API', type: 'string'),
        new Property(property: 'username', description: 'Никнейм', type: 'string'),
        new Property(property: 'email', description: 'E-mail адрес', type: 'string'),
        new Property(property: 'is_email_confirmed', description: 'Подтвержден ли адрес', type: 'boolean')
    ])]
    final public function getProfile(): array
    {
        return [
            'id' => $this->id,
            'access_token' => $this->authKey,
            'username' => $this->username,
            'email' => $this->email->value ?? null,
            'is_email_confirmed' => (bool)($this->email->is_confirmed ?? null)
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors(): array
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
    public function rules(): array
    {
        return [
            [['last_login_at', 'created_at', 'updated_at', 'status', 'last_ip'], 'integer'],
            Status::validator('status'),
            [['username', 'auth_source'], 'string', 'max' => 255],
            ['password_reset_token', 'string', 'max' => 50],
            ['password_reset_token', 'unique'],
            ['password_hash', 'string', 'max' => 60],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function externalAttributes(): array
    {
        return [
            'email.value',
            'email.is_confirmed',
            'userExt.first_name',
            'userExt.middle_name',
            'userExt.last_name',
            'userExt.phone',
            'userExt.rules_accepted'
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'id' => Yii::t(Module::MODULE_MESSAGES, 'ID'),
            'username' => Yii::t(Module::MODULE_MESSAGES, 'Username'),
            'password_hash' => Yii::t(Module::MODULE_MESSAGES, 'Password Hash'),
            'auth_source' => Yii::t(Module::MODULE_MESSAGES, 'Auth Source'),
            'password_reset_token' => Yii::t(Module::MODULE_MESSAGES, 'Password Reset Token'),
            'last_login_at' => Yii::t(Module::MODULE_MESSAGES, 'Last Login At'),
            'created_at' => Yii::t(Module::MODULE_MESSAGES, 'Created At'),
            'updated_at' => Yii::t(Module::MODULE_MESSAGES, 'Updated At'),
            'status' => Yii::t(Module::MODULE_MESSAGES, 'Status'),
            'last_ip' => Yii::t(Module::MODULE_MESSAGES, 'Last Ip')
        ];
    }

    /**
     * Сохраняем последний ip пользователя
     *
     * {@inheritdoc}
     *
     * @throws Exception
     */
    public function beforeSave($insert): bool
    {
        if (!$this->username) {
            $this->generateUsername();
        }
        return parent::beforeSave($insert);
    }

    /**
     * Сгенерировать случайное имя пользователя
     *
     * @throws Exception
     */
    final public function generateUsername(): void
    {
        $this->username = 'User_' . Yii::$app->security->generateRandomString(8) . time();
    }

    final public function getEmail(): ActiveQuery
    {
        return $this->hasOne(Email::class, ['user_id' => 'id'])->inverseOf('user');
    }

    final public function getUserExt(): ActiveQuery
    {
        return $this->hasOne(UserExt::class, ['user_id' => 'id'])->inverseOf('user');
    }

    final public function getSocialNetworks(): ActiveQuery
    {
        return $this->hasMany(SocialNetwork::class, ['user_id' => 'id'])->inverseOf('user');
    }

    final public function getSocialNetworkById(string $id): ?SocialNetwork
    {
        /** @var SocialNetwork|null $socialNetwork */
        $socialNetwork = $this->getSocialNetworks()->where(['social_network_id' => $id])->one();
        return $socialNetwork;
    }

    public static function getColumns(): array
    {
        Module::initI18N();
        return [
            'id',
            'username',
            'auth_source',
            'userExt.first_name',
            'userExt.middle_name',
            'userExt.last_name',
            'email.value',
            'last_login_at:datetime',
            'created_at:datetime'
        ];
    }
}
