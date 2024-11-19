<?php

namespace api\modules\v1\controllers;

use common\modules\user\{actions\AssignSocAction,
    actions\EmailConfirmAction,
    actions\EmailConfirmSendAction,
    actions\LoginAction,
    actions\LogoutAction,
    actions\PasswordResetAction,
    actions\PasswordRestoreAction,
    actions\ProfileAction,
    actions\ServiceDataGetAction,
    actions\ServiceDataSaveAction,
    actions\SignupAction,
    actions\UpdateAction,
    Module};
use yii\helpers\ArrayHelper;
use yii\web\BadRequestHttpException;

/**
 * Class UserController
 *
 * @package controllers
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
final class UserController extends AppController
{
    /**
     * {@inheritdoc}
     * @throws BadRequestHttpException
     */
    public function beforeAction($action): bool
    {
        Module::initI18N();
        return parent::beforeAction($action);
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors(): array
    {
        return ArrayHelper::merge(parent::behaviors(), [
            'auth' => ['except' => ['login', 'signup', 'password-restore', 'password-reset', 'email-confirm']]
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function actions(): array
    {
        return [
            'signup' => SignupAction::class,
            'login' => LoginAction::class,
            'assign-soc' => AssignSocAction::class,
            'logout' => LogoutAction::class,
            'update' => UpdateAction::class,
            'profile' => ProfileAction::class,
            'email-confirm-send' => EmailConfirmSendAction::class,
            'email-confirm' => EmailConfirmAction::class,
            'password-restore' => PasswordRestoreAction::class,
            'password-reset' => PasswordResetAction::class,
            'service-data-save' => ServiceDataSaveAction::class,
            'service-data-get' => ServiceDataGetAction::class
        ];
    }
}
