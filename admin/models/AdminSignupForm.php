<?php

namespace admin\models;

use common\models\AppModel;
use Yii;
use yii\base\Exception;

/**
 * Форма регистрации нового администратора
 *
 * @package models
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
class AdminSignupForm extends AppModel
{
    /**
     * Имя пользователя
     */
    public ?string $username = null;

    /**
     * Email адрес
     */
    public ?string $email = null;

    /**
     * Пароль
     */
    public ?string $password = null;

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['username', 'email'], 'trim'],
            [['username', 'email', 'password'], 'required'],
            [
                'username',
                'unique',
                'targetClass' => UserAdmin::class,
                'message' => Yii::t('app', 'This username has already been taken.')
            ],
            ['username', 'string', 'min' => 2, 'max' => 150],
            [
                'username',
                'match',
                'pattern' => '/^[А-яA-z0-9_]*$/u',
                'message' => Yii::t('app/error', 'Used unacceptable symbols')
            ],
            ['email', 'email'],
            ['email', 'string', 'max' => 150],
            [
                'email',
                'unique',
                'targetClass' => UserAdmin::class,
                'message' => Yii::t('app', 'This email address has already been taken.')
            ],
            [
                'password',
                'string',
                'min' => 6,
                'message' => Yii::t('app', 'Password must consist of at least 6 characters')
            ]
        ];
    }

    /**
     * {@inheritdoc}
     */
    final public function attributeLabels(): array
    {
        return [
            'username' => Yii::t('app', 'Login'),
            'email' => Yii::t('app', 'E-mail'),
            'password' => Yii::t('app', 'Password')
        ];
    }

    /**
     * {@inheritdoc}
     */
    final public function attributeHints(): array
    {
        return [
            'username' => Yii::t('app', 'Only words and numbers allowed, use "_" instead of spaces')
        ];
    }

    /**
     * Signs user up.
     *
     * @throws Exception
     * @throws \Exception
     */
    final public function signup(): ?UserAdmin
    {
        if (!$this->validate()) {
            return null;
        }
        $admin = new UserAdmin(['scenario' => UserAdmin::SCENARIO_REGISTER]);
        $admin->username = $this->username;
        $admin->email = $this->email;
        $admin->setPassword($this->password);
        $admin->generateAuthKey();
        if (!$admin->save()) {
            return null;
        }
        $auth = Yii::$app->authManager;
        $auth->assign($auth->getRole('admin'), $admin->getId());
        return $admin;
    }
}
