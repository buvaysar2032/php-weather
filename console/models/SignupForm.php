<?php

namespace console\models;

use admin\models\{AdminSignupForm, UserAdmin};

/**
 * Signup form
 *
 * @package console\models
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
final class SignupForm extends AdminSignupForm
{
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
                'message' => 'This username has already been taken.'
            ],
            ['username', 'string', 'min' => 2, 'max' => 150],
            ['username', 'match', 'pattern' => '/^[А-яA-z0-9_]*$/u', 'message' => 'Used unacceptable symbols'],
            ['email', 'email'],
            ['email', 'string', 'max' => 150],
            [
                'email',
                'unique',
                'targetClass' => UserAdmin::class,
                'message' => 'This email address has already been taken.'
            ],
            ['password', 'string', 'min' => 6, 'message' => 'Password must consist of at least 6 characters'],
        ];
    }
}
