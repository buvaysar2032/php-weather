<?php

namespace admin\components\auth;

use yii\filters\{AccessControl, AccessRule};
use Yii;
use yii\base\InvalidRouteException;
use yii\web\ForbiddenHttpException;

/**
 * Правило доступа только для админа
 */
class AdminAccessControl extends AccessControl
{
    public ?array $actions = null;

    public array|string $redirectUrl = ['index'];

    /**
     * {@inheritdoc}
     */
    public function init(): void
    {
        parent::init();
        $this->only = $this->actions;
        $this->rules[] = new AccessRule([
            'allow' => true, 'actions' => $this->actions, 'roles' => ['admin']
        ]);
    }

    /**
     * @throws InvalidRouteException
     * @throws ForbiddenHttpException
     */
    protected function denyAccess($user): void
    {
        if ($user !== false && $user->getIsGuest()) {
            $user->loginRequired();
        } else {
            Yii::$app->response->redirect($this->redirectUrl);
            Yii::$app->session->addFlash('error', Yii::t('yii', 'You are not allowed to perform this action.'));
        }
    }
}