<?php

namespace common\modules\user\models;

use common\components\exceptions\ModelSaveException;
use common\enums\Boolean;
use common\models\AppActiveRecord;
use common\modules\user\{enums\Status, Module};
use Yii;
use yii\base\Exception;
use yii\base\InvalidConfigException;
use yii\db\ActiveQuery;
use yii\web\NotFoundHttpException;

/**
 * This is the model class for table "{{%email}}".
 *
 * @package user\models
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 *
 * @property int       $id            [int] ID
 * @property int       $user_id       [int] ID пользователя
 * @property string    $value         [varchar(255)] Значение
 * @property string    $confirm_token [varchar(255)] Токен подтверждения
 * @property int       $is_confirmed  [tinyint(1)] Адрес подтвержден
 *
 * @property-read User $user
 */
class Email extends AppActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return '{{%email}}';
    }

    /**
     * Подтверждение email адреса
     *
     * @throws ModelSaveException
     */
    public static function confirm($token): array|bool
    {
        if (!$email = self::findOne(['confirm_token' => $token])) {
            return ['error' => 'token_is_not_valid'];
        }
        if ($email->is_confirmed) {
            return ['error' => 'email_is_confirmed'];
        }
        $email->is_confirmed = Boolean::Yes->value;
        $email->confirm_token = null;
        if (!$email->save()) {
            throw new ModelSaveException($email);
        }
        $user = $email->user;
        if ($user->status === Status::New->value) {
            $user->status = Status::Active->value;
            if (!$user->save()) {
                throw new ModelSaveException($user);
            }
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    final public function rules(): array
    {
        return [
            [['user_id', 'value'], 'required'],
            ['user_id', 'integer'],
            ['is_confirmed', 'boolean'],
            [['value', 'confirm_token'], 'string', 'max' => 255],
            ['value', 'email'],
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
            'value' => Yii::t(Module::MODULE_MESSAGES, 'E-mail'),
            'confirm_token' => Yii::t(Module::MODULE_MESSAGES, 'Confirm Token'),
            'is_confirmed' => Yii::t(Module::MODULE_MESSAGES, 'Is Confirmed'),
        ];
    }

    final public function getUser(): ActiveQuery
    {
        return $this->hasOne(User::class, ['id' => 'user_id'])->inverseOf('email');
    }

    /**
     * @throws Exception
     */
    final public function generateConfirmToken(): void
    {
        $this->confirm_token = Yii::$app->security->generateRandomString() . '_' . time();
    }

    /**
     * Отправка письма для подтверждения почты
     *
     * @throws Exception
     * @throws InvalidConfigException
     * @throws NotFoundHttpException
     */
    public function sendVerificationEmail(bool $generateNewToken = true): void
    {
        /** @var Module $userModule */
        $userModule = Yii::$app->getModule('user');
        if ($generateNewToken) {
            $confirm_token = Yii::$app->security->generateRandomString();
            $this->confirm_token = $confirm_token;
            $this->save();
        }
        $data['confirm_token'] = $this->confirm_token;
        Yii::$app->mailer->compose($userModule->verificationEmailTemplate, $data, $this->value)
            ->setSubject(Yii::t(Module::MODULE_MESSAGES, 'Email confirmation'))
            ->sendAsync();
    }
}
