<?php

namespace admin\modules\rbac\base;

use admin\controllers\AdminController;
use admin\modules\rbac\Module;
use admin\modules\rbac\models\AuthItemModel;
use admin\modules\rbac\models\search\AuthItemSearch;
use Yii;
use yii\base\{Exception, InvalidConfigException};
use yii\filters\{ContentNegotiator, VerbFilter};
use yii\helpers\ArrayHelper;
use yii\rbac\Item;
use yii\web\{NotFoundHttpException, Response};

/**
 * Class ItemController
 *
 * @package admin\modules\rbac\base
 */
class ItemController extends AdminController
{
    /**
     * Search class name for auth items search
     */
    public string|array $searchClass = [
        'class' => AuthItemSearch::class,
    ];

    /**
     * Type of Auth Item
     */
    protected int $type;

    /**
     * Labels use in view
     */
    protected array $labels;

    /**
     * @inheritdoc
     */
    public function behaviors(): array
    {
        return ArrayHelper::merge(parent::behaviors(), [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'index' => ['get'],
                    'view' => ['get'],
                    'create' => ['get', 'post'],
                    'update' => ['get', 'post'],
                    'delete' => ['post'],
                    'assign' => ['post'],
                    'remove' => ['post'],
                ],
            ],
            'contentNegotiator' => [
                'class' => ContentNegotiator::class,
                'only' => ['assign', 'remove'],
                'formats' => [
                    'application/json' => Response::FORMAT_JSON,
                ]
            ]
        ]);
    }

    /**
     * Lists of all auth items
     *
     * @throws InvalidConfigException
     */
    public function actionIndex(): string
    {
        $searchModel = Yii::createObject($this->searchClass);
        $searchModel->type = $this->type;
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
        ]);
    }

    /**
     * Displays a single AuthItem model.
     *
     * @throws NotFoundHttpException
     */
    public function actionView(string $id): string
    {
        $model = $this->findModel($id);

        return $this->render('view', ['model' => $model]);
    }

    /**
     * Creates a new AuthItem model.
     *
     * If creation is successful, the browser will be redirected to the 'view' page.
     */
    public function actionCreate(): Response|string
    {
        $model = new AuthItemModel();
        $model->type = $this->type;

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', Yii::t(Module::MODULE_MESSAGES, 'Item has been saved.'));

            return $this->redirect(['view', 'id' => $model->name]);
        }

        return $this->render('create', ['model' => $model]);
    }

    /**
     * Updates an existing AuthItem model.
     *
     * If update is successful, the browser will be redirected to the 'view' page.
     *
     * @throws NotFoundHttpException
     */
    public function actionUpdate(string $id): Response|string
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', Yii::t(Module::MODULE_MESSAGES, 'Item has been saved.'));

            return $this->redirect(['view', 'id' => $model->name]);
        }

        return $this->render('update', ['model' => $model]);
    }

    /**
     * Deletes an existing AuthItem model.
     *
     * If deletion is successful, the browser will be redirected to the 'index' page.
     *
     * @throws NotFoundHttpException
     */
    public function actionDelete(string $id): Response
    {
        $model = $this->findModel($id);
        Yii::$app->authManager->remove($model->item);
        Yii::$app->session->setFlash('success', Yii::t(Module::MODULE_MESSAGES, 'Item has been removed.'));

        return $this->redirect(['index']);
    }

    /**
     * Assign items
     *
     * @throws NotFoundHttpException
     * @throws Exception
     */
    public function actionAssign(string $id): array
    {
        $items = Yii::$app->getRequest()->post('items', []);
        $model = $this->findModel($id);
        $model->addChildren($items);

        return array_merge($model->getItems());
    }

    /**
     * Remove items
     *
     * @throws NotFoundHttpException
     */
    public function actionRemove(string $id): array
    {
        $items = Yii::$app->getRequest()->post('items', []);
        $model = $this->findModel($id);
        $model->removeChildren($items);

        return array_merge($model->getItems());
    }

    /**
     * {@inheritdoc}
     */
    public function getViewPath(): string
    {
        return $this->module->getViewPath() . DIRECTORY_SEPARATOR . 'item';
    }

    public function getType(): int
    {
        return $this->type;
    }

    public function getLabels(): array
    {
        return $this->labels;
    }

    /**
     * Finds the AuthItem model based on its primary key value.
     *
     * If the model is not found, a 404 HTTP exception will be thrown.
     *
     * @return AuthItemModel the loaded model
     *
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel(string $id): AuthItemModel
    {
        $auth = Yii::$app->authManager;
        $item = $this->type === Item::TYPE_ROLE ? $auth->getRole($id) : $auth->getPermission($id);

        if ($item === null) {
            throw new NotFoundHttpException(Yii::t(Module::MODULE_MESSAGES, 'The requested page does not exist.'));
        }

        return new AuthItemModel($item);
    }
}
