<?php

namespace admin\models;

use common\models\AppModel;
use Yii;
use yii\base\Exception;

/**
 * Форма смены пароля администратора
 *
 * @package models
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 *
 * @property-read string $username
 * @property-read int    $userAdminId
 */
class PasswordChangeForm extends AppModel
{
    /**
     * Текущий пароль
     */
    public ?string $currentPassword = null;

    /**
     * Новый пароль
     */
    public ?string $newPassword = null;

    /**
     * Повторение нового пароля
     */
    public ?string $newPasswordRepeat = null;

    /**
     * Модель пользователя
     */
    private UserAdmin $_user;

    public function __construct(UserAdmin $user, array $config = [])
    {
        $this->_user = $user;
        parent::__construct($config);
    }

    final public function getUsername(): string
    {
        return $this->_user->username;
    }

    final public function getUserAdminId(): int
    {
        return $this->_user->id;
    }

    /**
     * {@inheritdoc}
     */
    final public function rules(): array
    {
        return [
            [['currentPassword', 'newPassword', 'newPasswordRepeat'], 'required'],
            ['currentPassword', 'currentPassword'],
            ['newPassword', 'string', 'min' => 6],
            ['newPasswordRepeat', 'compare', 'compareAttribute' => 'newPassword']
        ];
    }

    /**
     * {@inheritdoc}
     */
    final public function attributeLabels(): array
    {
        return [
            'newPassword' => Yii::t('app', 'New Password'),
            'newPasswordRepeat' => Yii::t('app', 'Repeat Password'),
            'currentPassword' => Yii::t('app', 'Current Password'),
        ];
    }

    /**
     * Проверка текущего пароля
     */
    final public function currentPassword(string $attribute): void
    {
        if (!$this->hasErrors() && !$this->_user->validatePassword($this->$attribute)) {
            $this->addError($attribute, Yii::t('app', 'Wrong Current Password'));
        }
    }

    /**
     * Смена пароля
     *
     * @throws Exception
     */
    final public function changePassword(): bool
    {
        if ($this->validate()) {
            $user = $this->_user;
            $user->setPassword($this->newPassword);
            // Генерация нового токена для сброса авторизации
            $user->generateAuthKey();
            return $user->save();
        }
        return false;
    }
}
