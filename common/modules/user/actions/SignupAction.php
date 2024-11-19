<?php

namespace common\modules\user\actions;

use api\behaviors\returnStatusBehavior\{JsonError, JsonSuccess, RequestFormData};
use common\components\exceptions\ModelSaveException;
use common\modules\user\{helpers\UserHelper, Module, models\SignupForm};
use OpenApi\Attributes\{Items, Post, Property};
use Throwable;
use Yii;
use yii\base\{Exception, InvalidConfigException};
use yii\db\StaleObjectException;
use yii\web\{HttpException, Response};

/**
 * Регистрация пользователя
 *
 * @package user\actions
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
#[Post(
    path: '/user/signup',
    operationId: 'signup',
    description: 'Регистрация нового пользователя',
    summary: 'Регистрация',
    tags: ['user']
)]
#[RequestFormData(
    requiredProps: ['username', 'email', 'password'],
    properties: [
        new Property(property: 'username', description: 'Никнейм', type: 'string'),
        new Property(property: 'email', description: 'E-mail адрес', type: 'string'),
        new Property(property: 'password', description: 'Пароль', type: 'string'),
        new Property(
            property: 'rules_accepted', description: 'Согласие с правилами: 0 - отказ, 1 - принял', type: 'integer'
        )
    ]
)]
#[JsonSuccess(content: [new Property(property: 'profile', ref: '#/components/schemas/Profile')])]
#[JsonError(
    description: 'Validation error',
    content: [
        new Property(
            property: 'username', type: 'array',
            items:    new Items(type: 'string', example: 'Пользователь с таким логином уже зарегистрирован')
        ),
        new Property(
            property: 'email', type: 'array',
            items:    new Items(type: 'string', example: 'Такой Email уже зарегистрирован')
        ),
        new Property(
            property: 'rules_accepted', type: 'array',
            items:    new Items(type: 'string', example: 'Необходимо согласиться с правилами')
        )
    ]
)]
class SignupAction extends BaseAction
{
    /**
     * @throws Throwable
     * @throws ModelSaveException
     * @throws Exception
     * @throws InvalidConfigException
     * @throws StaleObjectException
     */
    final public function run(): Response|array|string|null
    {
        Module::initI18N();
        $soc = Yii::$app->request->getParameter('soc');
        $code = Yii::$app->request->getParameter('code');
        $error = Yii::$app->request->getParameter('error');
        /** @var Module $userModule */
        $userModule = Yii::$app->getModule('user');
        //Если пользователь нажал на "Отмена" при авторизации через соц. сеть.
        if ($error) {
            return $this->controller->returnOpenerResponse(['error' => ['login:error' => $error]]);
        }
        //Если разрешена регистрация через соц. сети, проверяем наличие id соц. сети в запросе
        if (($soc || $code) && $userModule->enableSocAuthorization === true) {
            return $this->socAuth('signup');
        }
        return $this->emailSignUp();
    }

    /**
     * Регистрация по e-mail.
     *
     * @throws ModelSaveException
     * @throws Exception
     * @throws HttpException
     */
    private function emailSignUp(): array
    {
        Module::initI18N();
        if (!Yii::$app->params['signup']['enabled_clients']['email-password']) {
            return $this->controller->returnError(Yii::t(Module::MODULE_ERROR_MESSAGES, 'Registration disabled'));
        }
        $form = new SignupForm();
        $form->load(Yii::$app->request->post(), '');
        if (!$user = $form->signup()) {
            return $this->controller->returnError('Validation error', $form->errors);
        }

        return $this->controller->returnSuccess(UserHelper::getProfile($user), 'profile');
    }
}
