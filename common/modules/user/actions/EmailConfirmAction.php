<?php

namespace common\modules\user\actions;

use common\components\exceptions\ModelSaveException;
use common\modules\user\models\Email;
use Exception;
use OpenApi\Attributes\{Get, Parameter, Response, Schema};
use Yii;
use yii\web\Response as WebResponse;

/**
 * Подтверждение почты пользователя
 *
 * @package user\actions
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
#[Get(
    path: '/user/email-confirm',
    operationId: 'email-confirm',
    description: 'Подтверждение почты токеном из письма',
    summary: 'Подтверждение почты',
    tags: ['user'],
)]
#[Response(response: 302, description: 'Redirect to frontend page with "confirm_status" parameter')]
class EmailConfirmAction extends BaseAction
{
    /**
     * @throws ModelSaveException
     * @throws Exception
     */
    final public function run(
        #[Parameter(name: 'token', description: 'Токен из письма подтверждения',
            in: 'query', required: false, schema: new Schema(type: 'string'))]
        string $token
    ): WebResponse {
        $redirect_url = Yii::$app->request->hostInfo . '/?confirm_status=';
        $result = Email::confirm($token);
        if (isset($result['error'])) {
            return $this->controller->redirect($redirect_url . $result['error']);
        }
        return $this->controller->redirect($redirect_url . 'success');
    }
}
