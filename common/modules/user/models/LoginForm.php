<?php

namespace common\modules\user\models;

use common\components\exceptions\ModelSaveException;
use common\models\AppModel;
use common\widgets\reCaptcha\ReCaptchaValidator3;
use Yii;
use yii\base\Exception;
use yii\web\HttpException;

/**
 * Форма авторизации
 *
 * @package models
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 * @property-read User $user
 */
class LoginForm extends AppModel
{
    /**
     * Имя пользователя или Email адрес
     */
    public ?string $login = null;

    /**
     * Пароль
     */
    public ?string $password = null;

    /**
     * Запомнить меня
     */
    public bool $rememberMe = true;

    /**
     * Google ReCaptcha V3
     */
    public ?string $reCaptcha = null;

    /**
     * Найденная User модель
     */
    private ?User $_user;

    /**
     * {@inheritdoc}
     */
    final public function rules(): array
    {
        $rules = [
            [['login', 'password'], 'required'],
            ['rememberMe', 'boolean'],
            ['password', 'validatePassword'],
        ];
        // Если настроена ReCaptcha, то добавляем защиту от спама
        if (!YII_ENV_TEST && !empty(Yii::$app->reCaptcha->secretV3)) {
            $rules[] = ['reCaptcha', 'required'];
            $rules[] = ['reCaptcha', ReCaptchaValidator3::class, 'action' => false];
        }
        return $rules;
    }

    /**
     * {@inheritdoc}
     */
    final public function attributeLabels(): array
    {
        return [
            'login' => Yii::t('app', 'Username'),
            'password' => Yii::t('app', 'Password'),
            'rememberMe' => Yii::t('app', 'Remember Me'),
        ];
    }

    /**
     * Validates the password.
     * This method serves as the inline validation for password.
     *
     * @param string $attribute the attribute currently being validated
     */
    final public function validatePassword(string $attribute): void
    {
        if (!$this->hasErrors()) {
            $user = $this->user;
            if (!$user || !$user->validatePassword($this->password)) {
                $this->password = '';
                $this->addError($attribute, Yii::t('app', 'Incorrect username or password'));
            }
        }
    }

    /**
     * Logs in a user using the provided username and password.
     *
     * @return bool whether the user is logged in successfully
     * @throws ModelSaveException
     * @throws Exception
     * @throws HttpException
     */
    final public function login(): bool
    {
        if ($this->validate()) {
            return $this->user->login(User::AUTH_SOURCE_EMAIL, $this->rememberMe ? 3600 * 24 * 30 : 0);
        }
        return false;
    }

    /**
     * Finds user by [[username]]
     *
     * @throws HttpException
     */
    final public function getUser(): ?User
    {
        if (!isset($this->_user)) {
            $this->_user = User::findIdentityByUsername($this->login);
            if (!$this->_user) {
                $this->_user = User::findIdentityByEmail($this->login);
            }
        }
        return $this->_user;
    }
}
