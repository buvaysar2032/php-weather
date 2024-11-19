<?php

namespace common\modules\user\models;

use common\components\exceptions\ModelSaveException;
use common\modules\user\{enums\PasswordRestoreType, helpers\UserHelper, Module};
use Yii;
use yii\base\Exception;
use yii\web\HttpException;

/**
 * Trait ResettablePassword
 *
 * @package user\models
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
trait ResettablePassword
{
    /**
     * Find user by password reset token
     *
     * @throws HttpException
     */
    public static function findIdentityByPasswordResetToken(string $token): ?self
    {
        if (!$user = self::findOne(['password_reset_token' => $token])) {
            return null;
        }
        return UserHelper::checkUserStatus($user);
    }

    /**
     * Finds out if password reset token is valid
     */
    public static function isPasswordResetTokenValid(?string $token): bool
    {
        if (empty($token)) {
            return false;
        }
        $timestamp = (int)substr($token, strrpos($token, '_') + 1);
        $expire = Yii::$app->params['user.passwordResetTokenExpire'];
        return $timestamp + $expire >= time();
    }

    /**
     * Remove password reset token
     */
    final public function removePasswordResetToken(): void
    {
        $this->password_reset_token = null;
        $this->save();
    }

    /**
     * Generate password reset token
     *
     * @throws Exception
     */
    final public function generatePasswordResetToken(): void
    {
        $this->password_reset_token = Yii::$app->security->generateRandomString() . '_' . time();
    }

    /**
     * Validate password
     */
    final public function validatePassword(string $password): bool
    {
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }

    /**
     * Set new password
     *
     * @throws Exception
     */
    final public function setPassword(string $password): void
    {
        $this->password_hash = Yii::$app->security->generatePasswordHash($password);
    }

    /**
     * @throws Exception
     */
    final public function resetPassword(): bool|int|string|null
    {
        /** @var Module $userModule */
        $userModule = Yii::$app->getModule('user');
        return match ($userModule->passwordRestoreType) {
            PasswordRestoreType::Directly => $this->restorePasswordDirectlyToEmail(),
            PasswordRestoreType::ViaToken => $this->restorePasswordViaToken()
        };
    }

    /**
     * Отправка нового пароля напрямую в письме
     *
     * @throws Exception
     */
    private function restorePasswordDirectlyToEmail(): bool|int|string|null
    {
        /** @var Module $userModule */
        $userModule = Yii::$app->getModule('user');
        // Создаём новый пароль
        $newPassword = Yii::$app->security->generateRandomString(16);
        $this->setPassword($newPassword);
        if (!$this->save(false)) {
            throw new ModelSaveException($this);
        }
        // Отправляем письмо с паролем
        $data['password'] = $newPassword;
        return Yii::$app->mailer->compose($userModule->passwordSendTemplate, $data, $this->email->value)
            ->setSubject(Yii::t(Module::MODULE_MESSAGES, 'Password Recovery'))
            ->sendAsync();
    }

    /**
     * Отправка токена восстановления
     *
     * @throws Exception
     */
    private function restorePasswordViaToken(): bool|int|string|null
    {
        /** @var Module $userModule */
        $userModule = Yii::$app->getModule('user');
        if (!User::isPasswordResetTokenValid($this->password_reset_token)) {
            $this->generatePasswordResetToken();
            if (!$this->save(false)) {
                throw new ModelSaveException($this);
            }
        }
        return Yii::$app->mailer->compose(view: $userModule->passwordTokenTemplate, to: $this->email->value)
            ->setSubject(Yii::t(Module::MODULE_MESSAGES, 'Password Recovery'))
            ->sendAsync();
    }
}
