<?php

namespace common\modules\user\actions;

use api\behaviors\returnStatusBehavior\{JsonError, JsonSuccess};
use OpenApi\Attributes\{Get, Property};
use Yii;
use yii\helpers\Json;

/**
 * Получение служебной информации по пользователю
 *
 * @package user\actions
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
#[Get(
    path: '/user/service-data-get',
    operationId: 'service-data-get',
    description: 'Получение сохраненных сервисных данных',
    summary: 'Получение сервисных данных',
    security: [['bearerAuth' => []]],
    tags: ['user'],
)]
#[JsonSuccess(content: [
    new Property(property: 'service_data', description: 'Введенные ранее данные', type: 'object')
])]
#[JsonError(description: 'ERROR no data', content: [
    new Property(property: 'service_data:error', type: 'string', example: 'No service data')
])]
class ServiceDataGetAction extends BaseAction
{
    final public function run(): array
    {
        if (!$data = Yii::$app->user->identity->userExt->service_data) {
            return $this->controller->returnError('service_data:error', 'No service data');
        }
        return $this->controller->returnSuccess(Json::decode($data), 'service_data');
    }
}
