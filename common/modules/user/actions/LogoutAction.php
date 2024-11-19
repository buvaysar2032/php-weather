<?php

namespace common\modules\user\actions;

use api\behaviors\returnStatusBehavior\JsonSuccess;
use common\components\exceptions\ModelSaveException;
use common\modules\user\{models\User, Module};
use OpenApi\Attributes\{Post, Property};
use Throwable;
use Yii;
use yii\db\StaleObjectException;

/**
 * Выход пользователя из сети
 *
 * @package user\actions
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
#[Post(
    path: '/user/logout',
    operationId: 'logout',
    description: 'Выход',
    summary: 'Выход',
    security: [['bearerAuth' => []]],
    tags: ['user']
)]
#[JsonSuccess(content: [new Property(property: 'user', example: 'You have successfully logged out')])]
class LogoutAction extends BaseAction
{
    /**
     * @throws Throwable
     * @throws ModelSaveException
     * @throws StaleObjectException
     */
    final public function run(): array
    {
        if (User::logout()) {
            return $this->controller->returnSuccess(
                ['user' => Yii::t(Module::MODULE_SUCCESS_MESSAGES, 'You have successfully logged out')]
            );
        }
        return $this->controller->returnSuccess(
            ['user' => Yii::t(Module::MODULE_SUCCESS_MESSAGES, 'You are already logged out')]
        );
    }
}
