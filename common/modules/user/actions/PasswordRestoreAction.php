<?php

namespace common\modules\user\actions;

use api\behaviors\returnStatusBehavior\{JsonError, JsonSuccess, RequestFormData};
use common\modules\user\{models\User, Module};
use OpenApi\Attributes\{Post, Property};
use Yii;
use yii\base\Exception;

/**
 * Запрос на восстановление пароля пользователя
 *
 * @package user\actions
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
#[Post(
    path: '/user/password-restore',
    operationId: 'password-restore',
    description: 'Восстановление пароля',
    summary: 'Восстановление пароля',
    tags: ['user']
)]
#[RequestFormData(properties: [new Property(property: 'email', description: 'E-mail адрес', type: 'string')])]
#[JsonSuccess(content: [
    new Property(property: 'mail', type: 'string', example: 'Password recovery email sent')
])]
#[JsonError(response: 503, content: [
    new Property(property: 'email', type: 'string', example: 'User is not found')
])]
#[JsonError(response: 508, content: [
    new Property(property: 'password_restore', type: 'string', example: 'Password restore is blocked')
])]
class PasswordRestoreAction extends BaseAction
{
    /**
     * @throws Exception
     */
    final public function run(): ?array
    {
        $email = Yii::$app->request->getParameter('email');
        /** @var Module $userModule */
        $userModule = Yii::$app->getModule('user');
        if (!$userModule->enablePasswordRestore) {
            return $this->controller->returnError(
                ['password_restore' => Yii::t(Module::MODULE_ERROR_MESSAGES, 'Password restore is blocked')],
                null,
                508
            );
        }
        $user = User::findIdentityByEmail($email);
        if (!$user) {
            return $this->controller->returnError(
                ['email' => Yii::t(Module::MODULE_ERROR_MESSAGES, 'User is not found')],
                null,
                503
            );
        }
        $result = $user->resetPassword();
        if ($result !== false) {
            return $this->controller->returnSuccess([
                'mail' => Yii::t(Module::MODULE_SUCCESS_MESSAGES, 'Password recovery email sent')
            ]);
        }
        return $this->controller->returnError([
            'email' => Yii::t(Module::MODULE_ERROR_MESSAGES, 'Message send error')
        ]);
    }
}
