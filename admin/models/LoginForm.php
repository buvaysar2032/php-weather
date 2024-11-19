<?php

namespace admin\models;

use common\models\AppModel;
use common\widgets\reCaptcha\ReCaptchaValidator3;
use Exception;
use Yii;
use yii\base\InvalidConfigException;
use yii\captcha\Captcha;

/**
 * Форма авторизации в панели администратора
 *
 * @package models
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 *
 * @property-read null|UserAdmin $user
 */
class LoginForm extends AppModel
{
    /**
     * UserName
     */
    public ?string $username = null;

    /**
     * Password
     */
    public ?string $password = null;

    /**
     * Remember me
     */
    public bool $rememberMe = true;

    /**
     * Google ReCaptcha V3
     */
    public ?string $reCaptcha = null;

    /**
     * @var string|null Simple Captcha
     */
    public ?string $verifyCode = null;

    /**
     * User model
     */
    private ?UserAdmin $_user;

    /**
     * {@inheritdoc}
     * @throws InvalidConfigException
     */
    final public function rules(): array
    {
        $rules = [
            // username and password are both required
            [['username', 'password'], 'required'],
            // rememberMe must be a boolean value
            ['rememberMe', 'boolean'],
            // password is validated by validatePassword()
            ['password', 'validatePassword']
        ];

        // Если настроена ReCaptcha, то добавляем защиту от спама
        if (!YII_ENV_TEST) {
            if (!empty(Yii::$app->reCaptcha->secretV3)) {
                $rules[] = ['reCaptcha', 'required'];
                $rules[] = ['reCaptcha', ReCaptchaValidator3::class];
            } else {
                $rules[] = [
                    'verifyCode',
                    'captcha',
                    'skipOnEmpty' => !Captcha::checkRequirements(),
                    'enableClientValidation' => false,
                    'captchaAction' => 'site/captcha'
                ];
            }
        }
        return $rules;
    }

    /**
     * {@inheritdoc}
     */
    final public function attributeLabels(): array
    {
        return [
            'username' => 'Имя пользователя',
            'rememberMe' => 'Запомнить',
            'password' => 'Пароль',
            'verifyCode' => 'Код подтверждения'
        ];
    }

    /**
     * Validates the password.
     *
     * This method serves as the inline validation for password.
     *
     * @param string $attribute the attribute currently being validated
     */
    final public function validatePassword(string $attribute): void
    {
        if (!$this->hasErrors()) {
            $user = $this->getUser();
            if (!$user || !$user->validatePassword($this->password)) {
                $this->password = '';
                $this->addError($attribute, Yii::t('app/error', 'Incorrect username or password.'));
            }
        }
    }

    /**
     * Finds user by [[username]]
     */
    final protected function getUser(): ?UserAdmin
    {
        if (!isset($this->_user)) {
            $this->_user = UserAdmin::findByUsername($this->username);
        }

        return $this->_user;
    }

    /**
     * Logs in a user using the provided username and password.
     *
     * @return bool whether the user is logged in successfully
     * @throws Exception
     */
    final public function login(): bool
    {
        if ($this->validate()) {
            return Yii::$app->user->login($this->getUser(), $this->rememberMe ? 3600 * 24 * 30 : 0);
        }
        return false;
    }
}
