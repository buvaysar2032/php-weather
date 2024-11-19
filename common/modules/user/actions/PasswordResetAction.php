<?php

namespace common\modules\user\actions;

use common\modules\user\{helpers\UserHelper, models\User, Module};
use Yii;
use yii\base\Exception;
use yii\web\HttpException;

/**
 * Сброс пароля (по токену)
 *
 * @package user\actions
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
class PasswordResetAction extends BaseAction
{
    /**
     * @throws Exception
     * @throws HttpException
     */
    final public function run(): array
    {
        //TODO: добавить ввод пароля (на стороне js)
        //Получаем новый пароль
        $password = Yii::$app->request->getParameter('password');
        /** @var Module $userModule */
        $userModule = Yii::$app->getModule('user');
        if (!$password) {
            return $this->controller->returnError(
                ['password:wrong' => Yii::t(Module::MODULE_ERROR_MESSAGES, 'Insert password')],
                500
            );
        }
        if (!$userModule->enablePasswordRestore) {
            return $this->controller->returnError(
                ['password_restore' => Yii::t(Module::MODULE_ERROR_MESSAGES, 'Password restore is blocked')]
            );
        }

        //Получаем пользователя
        $current_user = Yii::$app->user->identity;

        if (!$current_user) {
            $token = Yii::$app->request->getParameter('reset_password_token');
            if (!$token) {
                return $this->controller->returnError(
                    ['token:wrong' => Yii::t(Module::MODULE_ERROR_MESSAGES, 'Insert token')],
                    500
                );
            }

            $user = User::findIdentityByPasswordResetToken($token);
            if (!$user) {
                return $this->controller->returnError(
                    ['token:wrong' => Yii::t(Module::MODULE_ERROR_MESSAGES, 'Wrong token'), 'token' => $token],
                    500
                );
            }
        } else {
            $user = $current_user;
        }

        //Обновляем данные пользователя
        $user->setPassword($password);
        $user->removePasswordResetToken();
        if (!$user->save(false)) {
            return $this->controller->returnError(['validation-error' => $user->errors]);
        }

        // Authorize user
        Yii::$app->user->login($user);
        return $this->controller->returnSuccess(UserHelper::getProfile($user), 'profile');
    }
}