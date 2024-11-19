<?php

namespace admin\modules\rbac\filters;

use Exception;
use Yii;
use yii\base\{Action, Module};
use yii\helpers\{ArrayHelper, Url};

/**
 * Class AccessControl
 *
 * @package admin\modules\rbac\filters
 */
class AccessControl extends \yii\filters\AccessControl
{
    public array $params = [];

    /**
     * List of actions that not need to check access
     */
    public array $allowActions = [];

    /**
     * {@inheritdoc}
     *
     * @throws Exception
     */
    public function beforeAction($action): bool
    {
        return $this->isAllowed($action) || parent::beforeAction($action);
    }

    /**
     * @throws Exception
     */
    public function isAllowed(Action $action): bool
    {
        $controller = $action->controller;
        $params = ArrayHelper::getValue($this->params, $action->id, []);

        if (Yii::$app->user->can('/' . ltrim($action->getUniqueId(), '/'), $params)) {
            return true;
        }

        do {
            $permission = '/' . ltrim($controller->getUniqueId() . '/*', '/');
            if (Yii::$app->user->can($permission)) {
                return true;
            }
            $controller = $controller->module;
        } while ($controller !== null);
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function isActive($action): bool
    {
        if ($this->isErrorPage($action) || $this->isLoginPage($action) || $this->isAllowedAction($action)) {
            return false;
        }

        return parent::isActive($action);
    }

    /**
     * Returns a value indicating whether a current url equals `errorAction` property of the ErrorHandler component
     */
    private function isErrorPage(Action $action): bool
    {
        return $action->getUniqueId() === Yii::$app->getErrorHandler()->errorAction;
    }

    /**
     * Returns a value indicating whether a current url equals `loginUrl` property of the User component
     */
    private function isLoginPage(Action $action): bool
    {
        $loginUrl = trim(str_replace(Yii::$app->request->baseUrl, '', Url::to(Yii::$app->user->loginUrl)), '/');

        return Yii::$app->user->isGuest && $action->getUniqueId() === $loginUrl;
    }

    /**
     * Returns a value indicating whether a current url exists in the `allowActions` list.
     *
     * @param Action $action
     *
     * @return bool
     */
    private function isAllowedAction(Action $action): bool
    {
        if ($this->owner instanceof Module) {
            $ownerId = $this->owner->getUniqueId();
            $id = $action->getUniqueId();
            if (!empty($ownerId) && str_starts_with($id, $ownerId . '/')) {
                $id = substr($id, strlen($ownerId) + 1);
            }
        } else {
            $id = $action->id;
        }

        foreach ($this->allowActions as $route) {
            if (str_ends_with($route, '*')) {
                $route = rtrim($route, '*');
                if ($route === '' || str_starts_with($id, $route)) {
                    return true;
                }
            } elseif ($id === $route) {
                return true;
            }
        }

        return false;
    }
}
