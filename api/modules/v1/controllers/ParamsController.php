<?php

namespace api\modules\v1\controllers;

use api\behaviors\returnStatusBehavior\JsonSuccess;
use common\models\Param;
use Exception;
use OpenApi\Attributes\{Get, Parameter, Property, Schema};
use yii\helpers\ArrayHelper;

/**
 * Class ParamsController
 *
 * @package controllers
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 * @author  d.potehin <d.potehin@peppers-studio.ru>
 */
final class ParamsController extends AppController
{
    /**
     * {@inheritdoc}
     */
    public function behaviors(): array
    {
        return ArrayHelper::merge(parent::behaviors(), ['auth' => ['except' => ['index']]]);
    }

    /**
     * @throws Exception
     */
    #[Get(
        path: '/params/index',
        operationId: 'params-index',
        description: 'Возвращает полный список параметров',
        summary: 'Список публичных параметров',
        tags: ['params']
    )]
    #[JsonSuccess(
        content: [new Property(property: 'params', type: 'object', example: ['main' => ['key' => 'Hello World']])]
    )]
    public function actionIndex(
        #[Parameter(description: 'Название конкретной группы', in: 'query', schema: new Schema(type: 'string'))]
        string $group = null
    ): array {
        $params = Param::getAll($group);
        return $this->returnSuccess($params, 'params');
    }
}
