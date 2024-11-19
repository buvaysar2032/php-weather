<?php

namespace admin\models;

use admin\enums\AdminStatus;
use common\components\helpers\ModuleHelper;
use common\models\AppActiveRecord;
use Yii;
use yii\base\{Exception, NotSupportedException};
use yii\behaviors\TimestampBehavior;
use yii\helpers\ArrayHelper;
use yii\web\IdentityInterface;

/**
 * This is the model class for table "{{%user_admin}}".
 *
 * @package models
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 *
 * @property int          $id                   [int] ID
 * @property string       $username             [varchar(255)] Имя пользователя
 * @property string       $auth_key             [varchar(32)] Ключ авторизации
 * @property string       $password_hash        [varchar(255)] Хеш пароля
 * @property string       $password_reset_token [varchar(255)] Токен сброса пароля
 * @property string       $email                [varchar(255)] Email адрес
 * @property int          $status               [smallint] Статус
 * @property int          $created_at           [int] Дата создания
 * @property int          $updated_at           [int] Дата обновления
 *
 * @property-read string  $authKey              Ключ авторизации
 * @property-write string $password             Пароль
 */
class UserAdmin extends AppActiveRecord implements IdentityInterface
{
    /**
     * Сценарий модели для регистрации
     */
    public const SCENARIO_REGISTER = 'register';

    /**
     * Сценарий модели для обновления пароля
     */
    public const SCENARIO_UPDATE_PASSWORD = 'password';

    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return '{{%user_admin}}';
    }

    /**
     * {@inheritdoc}
     */
    public static function findIdentity($id): ?self
    {
        return static::findOne(['id' => $id, 'status' => AdminStatus::Active->value]);
    }

    /**
     * {@inheritdoc}
     *
     * @throws NotSupportedException
     */
    public static function findIdentityByAccessToken($token, $type = null): ?IdentityInterface
    {
        throw new NotSupportedException('"findIdentityByAccessToken" is not implemented.');
    }

    /**
     * Finds user by username
     */
    public static function findByUsername(string $username): ?self
    {
        return static::findOne(['username' => $username, 'status' => AdminStatus::Active->value]);
    }

    /**
     * Finds user by password reset token
     */
    public static function findByPasswordResetToken(string $token): ?self
    {
        if (!static::isPasswordResetTokenValid($token)) {
            return null;
        }

        return static::findOne(['password_reset_token' => $token, 'status' => AdminStatus::Active->value]);
    }

    /**
     * Finds out if password reset token is valid
     */
    public static function isPasswordResetTokenValid(string $token): bool
    {
        if (empty($token)) {
            return false;
        }

        $timestamp = (int)substr($token, strrpos($token, '_') + 1);
        $expire = Yii::$app->params['user.passwordResetTokenExpire'];
        return $timestamp + $expire >= time();
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors(): array
    {
        return ArrayHelper::merge(parent::behaviors(), ['timestamp' => ['class' => TimestampBehavior::class]]);
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['username', 'auth_key', 'password_hash', 'email'], 'required'],

            [['status', 'created_at', 'updated_at'], 'integer'],
            ['password_hash', 'string', 'max' => 255],
            ['auth_key', 'string', 'max' => 32],
            ['password_reset_token', 'string', 'max' => 50],
            [['username', 'email'], 'string', 'max' => 150],

            ['username', 'unique'],
            ['email', 'unique'],
            ['password_reset_token', 'unique'],

            ['username', 'match', 'pattern' => '/^[А-Яа-яa-zA-Z0-9_]*$/u'],
            ['email', 'email'],
            ['status', 'default', 'value' => AdminStatus::Active->value],
            AdminStatus::validator('status'),
            [
                'status',
                function (string $attribute) {
                    $errorMessages = 'app/error';
                    $currentUserAdmin = !ModuleHelper::isConsoleModule() ? Yii::$app->user->identity ?? null : null;
                    if (
                        $currentUserAdmin &&
                        $this->status === AdminStatus::Inactive->value &&
                        $this->id === $currentUserAdmin->id
                    ) {
                        $this->addError($attribute, Yii::t($errorMessages, 'Can not ban yourself'));
                    }
                    $admins = self::find()->where(['status' => AdminStatus::Active->value])->count();
                    if ($this->status === AdminStatus::Inactive->value && $admins <= 1) {
                        $this->addError($attribute, Yii::t($errorMessages, 'Last active administrator!'));
                    }
                }
            ]
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios(): array
    {
        return [
            self::SCENARIO_DEFAULT => [
                'username',
                'email',
                'password_reset_token',
                'status',
                'created_at',
                'updated_at'
            ],
            self::SCENARIO_REGISTER => [
                'username',
                'email',
                'auth_key',
                'password_hash',
                'password_reset_token',
                'status',
                'created_at',
                'updated_at'
            ],
            self::SCENARIO_UPDATE_PASSWORD => [
                'auth_key',
                'password_hash',
                'password_reset_token',
                'updated_at'
            ]
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'username' => Yii::t('app', 'Username'),
            'auth_key' => Yii::t('app', 'Auth Key'),
            'password_hash' => Yii::t('app', 'Password Hash'),
            'password_reset_token' => Yii::t('app', 'Password Reset Token'),
            'email' => Yii::t('app', 'Email'),
            'status' => Yii::t('app', 'Status'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At')
        ];
    }

    /**
     * @throws Exception
     */
    final public function setPassword(string $password): void
    {
        $this->password_hash = Yii::$app->security->generatePasswordHash($password);
    }

    final public function validatePassword(string $password): bool
    {
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }

    /**
     * {@inheritdoc}
     */
    final public function getId()
    {
        return $this->getPrimaryKey();
    }

    /**
     * {@inheritdoc}
     */
    final public function validateAuthKey($authKey): bool
    {
        return $this->getAuthKey() === $authKey;
    }

    /**
     * {@inheritdoc}
     */
    final public function getAuthKey(): string
    {
        return $this->auth_key;
    }

    /**
     * Generates "remember me" authentication key
     *
     * @throws Exception
     */
    final public function generateAuthKey(): void
    {
        $this->auth_key = Yii::$app->security->generateRandomString();
    }

    /**
     * Generates new password reset token
     *
     * @throws Exception
     */
    final public function generatePasswordResetToken(): void
    {
        $this->password_reset_token = Yii::$app->security->generateRandomString() . '_' . time();
    }

    /**
     * Removes password reset token
     */
    final public function removePasswordResetToken(): void
    {
        $this->password_reset_token = null;
    }
}
