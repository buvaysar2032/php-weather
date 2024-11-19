<?php

namespace common\modules\user\actions;

use api\behaviors\returnStatusBehavior\JsonSuccess;
use common\components\exceptions\ModelSaveException;
use common\modules\user\helpers\UserHelper;
use OpenApi\Attributes\{Get, Property};
use yii\base\Exception;
use yii\web\HttpException;

/**
 * Возвращение профиля пользователя
 *
 * @package user\actions
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
#[Get(
    path: '/user/profile',
    operationId: 'profile',
    description: 'Запрос данных профиля',
    summary: 'Данные профиля',
    security: [['bearerAuth' => []]],
    tags: ['user'],
)]
#[JsonSuccess(content: [new Property(property: 'profile', ref: '#/components/schemas/Profile')])]
class ProfileAction extends BaseAction
{
    /**
     * @throws ModelSaveException
     * @throws Exception
     * @throws HttpException
     */
    final public function run(): array
    {
        return $this->controller->returnSuccess(UserHelper::getProfile(), 'profile');
    }
}
