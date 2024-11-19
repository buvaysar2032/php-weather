<?php

namespace common\modules\user\actions;

use api\behaviors\returnStatusBehavior\JsonSuccess;
use common\components\exceptions\ModelSaveException;
use OpenApi\Attributes\{MediaType, Post, Property, RequestBody, Schema};
use Yii;
use yii\db\Exception;

/**
 * Запись служебной информации по пользователю
 *
 * @package user\actions
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
#[Post(
    path: '/user/service-data-save',
    operationId: 'service-data-save',
    description: 'Сохранение любых дополнительных данных',
    summary: 'Сохранение сервисных данных',
    security: [['bearerAuth' => []]],
    tags: ['user'],
)]
#[RequestBody(content: [new MediaType(mediaType: 'application/json', schema: new Schema(properties: []))])]
#[JsonSuccess(content: [
    new Property(property: 'message', type: 'string', example: 'Service data saved successfully')
])]
class ServiceDataSaveAction extends BaseAction
{
    /**
     * @throws ModelSaveException
     * @throws Exception
     */
    final public function run(): array
    {
        $data = Yii::$app->request->rawBody;
        $userExt = Yii::$app->user->identity->userExt;
        $userExt->service_data = $data;
        if (!$userExt->save()) {
            throw new ModelSaveException($userExt);
        }
        return $this->controller->returnSuccess('Service data saved successfully');
    }
}
