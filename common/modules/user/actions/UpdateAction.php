<?php

namespace common\modules\user\actions;

use api\behaviors\returnStatusBehavior\{JsonSuccess, RequestFormData};
use common\components\exceptions\ModelSaveException;
use common\enums\Boolean;
use common\modules\user\{helpers\UserHelper, models\User, Module};
use OpenApi\Attributes\{Post, Property};
use Yii;
use yii\base\{Exception, InvalidConfigException};
use yii\web\{HttpException, IdentityInterface};

/**
 * Обновление данных пользователя
 *
 * @package user\actions
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
#[Post(
    path: '/user/update',
    operationId: 'user-update',
    description: 'Изменение данных пользователя',
    summary: 'Изменение данных',
    security: [['bearerAuth' => []]],
    tags: ['user'],
)]
#[RequestFormData(properties: [
    new Property(property: 'email', description: 'E-mail адрес', type: 'string'),
    new Property(property: 'username', description: 'Никнейм', type: 'string'),
    new Property(property: 'first_name', description: 'Имя', type: 'string'),
    new Property(property: 'middle_name', description: 'Отчество', type: 'string'),
    new Property(property: 'last_name', description: 'Фамилия', type: 'string'),
    new Property(property: 'phone', description: 'Телефон', type: 'string')
])]
#[JsonSuccess(content: [new Property(property: 'profile', ref: '#/components/schemas/Profile')])]
class UpdateAction extends BaseAction
{
    /**
     * Список полей, разрешённых для редактирования
     */
    public array $fields = [];

    /**
     * Нужно ли отправлять для подтверждения почты в случае изменения адреса
     */
    public bool $sendMail;

    /**
     * {@inheritdoc}
     */
    final public function init(): void
    {
        parent::init();
        /** @var Module $userModule */
        $userModule = Yii::$app->getModule('user');
        $this->fields = $userModule->updateFields;
        $this->sendMail = $userModule->sendVerificationMessageIfEmailIsChanged;
    }

    /**
     * @throws ModelSaveException
     * @throws Exception
     * @throws InvalidConfigException
     * @throws HttpException
     */
    final public function run(): array
    {
        /** @var IdentityInterface|User $user */
        $user = Yii::$app->user->identity;
        $updateFields = $this->fields;
        foreach ($updateFields as $field) {
            $this->updateUserField($user, $field);
        }
        if (!$user->save() || !$user->userExt->save()) {
            return $this->controller->returnError(array_merge($user->errors, $user->userExt->errors));
        }
        return $this->controller->returnSuccess(UserHelper::getProfile($user), 'profile');
    }

    /**
     * Обновление полей пользователя
     *
     * @throws ModelSaveException
     * @throws Exception
     * @throws InvalidConfigException
     */
    private function updateUserField(User $user, string $field): void
    {
        $field_value = Yii::$app->request->getParameter($field);
        if (!$field_value) {
            return;
        }
        switch ($field) {
            case 'email':
                $email = $user->email;
                if ($email->value === $field_value) {
                    break;
                }
                $email->value = $field_value;
                $email->is_confirmed = Boolean::No->value;
                if ($this->sendMail === true) {
                    $email->sendVerificationEmail();
                }
                if (!$email->save()) {
                    throw new ModelSaveException($email);
                }
                break;
            case 'username':
                $user->username = $field_value;
                break;
            default:
                $user->userExt->$field = $field_value;
                break;
        }
    }
}
