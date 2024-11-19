<?php

namespace admin\modules\rbac\controllers;

use admin\controllers\AdminController;
use admin\modules\rbac\models\RouteModel;
use Exception;
use Yii;
use yii\base\InvalidConfigException;
use yii\filters\{ContentNegotiator, VerbFilter};
use yii\helpers\ArrayHelper;
use yii\web\Response;

/**
 * Class RouteController
 *
 * @package admin\modules\rbac\controllers
 */
class RouteController extends AdminController
{
    /**
     * Route model class
     */
    public string|array $modelClass = [
        'class' => RouteModel::class,
    ];

    /**
     * Returns a list of behaviors that this component should behave as.
     *
     * @return array
     */
    public function behaviors(): array
    {
        return ArrayHelper::merge(parent::behaviors(), [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'index' => ['get', 'post'],
                    'create' => ['post'],
                    'assign' => ['post'],
                    'remove' => ['post'],
                    'refresh' => ['post']
                ],
            ],
            'contentNegotiator' => [
                'class' => ContentNegotiator::class,
                'only' => ['assign', 'remove', 'refresh'],
                'formats' => ['application/json' => Response::FORMAT_JSON]
            ]
        ]);
    }

    /**
     * Lists all Route models.
     *
     * @throws InvalidConfigException
     */
    public function actionIndex(): string
    {
        /** @var RouteModel $model */
        $model = Yii::createObject($this->modelClass);

        return $this->render('index', ['routes' => $model->getAvailableAndAssignedRoutes()]);
    }

    /**
     * Assign routes
     *
     * @throws InvalidConfigException
     * @throws Exception
     */
    public function actionAssign(): array
    {
        $routes = Yii::$app->getRequest()->post('routes', []);
        /** @var RouteModel $model */
        $model = Yii::createObject($this->modelClass);
        $model->addNew($routes);

        return $model->getAvailableAndAssignedRoutes();
    }

    /**
     * Remove routes
     *
     * @throws InvalidConfigException
     */
    public function actionRemove(): array
    {
        $routes = Yii::$app->getRequest()->post('routes', []);
        /** @var RouteModel $model */
        $model = Yii::createObject($this->modelClass);
        $model->remove($routes);

        return $model->getAvailableAndAssignedRoutes();
    }

    /**
     * Refresh cache of routes
     *
     * @throws InvalidConfigException
     */
    public function actionRefresh(): array
    {
        /** @var RouteModel $model */
        $model = Yii::createObject($this->modelClass);
        $model->invalidate();

        return $model->getAvailableAndAssignedRoutes();
    }
}
