<?php

namespace admin\modules\rbac\controllers;

use admin\controllers\AdminController;
use admin\modules\rbac\models\{BizRuleModel, search\BizRuleSearch};
use admin\modules\rbac\Module;
use Yii;
use yii\base\InvalidConfigException;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\{NotFoundHttpException, Response};

/**
 * Class RuleController
 *
 * @package admin\modules\rbac\controllers
 */
class RuleController extends AdminController
{
    /**
     * Search class name for rules search
     */
    public string|array $searchClass = ['class' => BizRuleSearch::class];

    /**
     * Returns a list of behaviors that this component should behave as.
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
                    'delete' => ['post']
                ]
            ]
        ]);
    }

    /**
     * List of all rules
     *
     * @throws InvalidConfigException
     */
    public function actionIndex(): string
    {
        $searchModel = Yii::createObject($this->searchClass);
        $dataProvider = $searchModel->search(Yii::$app->request->getQueryParams());

        return $this->render('index', ['dataProvider' => $dataProvider, 'searchModel' => $searchModel]);
    }

    /**
     * Displays a single Rule item.
     *
     * @throws NotFoundHttpException
     */
    public function actionView(string $id): string
    {
        $model = $this->findModel($id);

        return $this->render('view', ['model' => $model]);
    }

    /**
     * Creates a new Rule item.
     *
     * If creation is successful, the browser will be redirected to the 'view' page.
     */
    public function actionCreate(): Response|string
    {
        $model = new BizRuleModel();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', Yii::t(Module::MODULE_MESSAGES, 'Rule has been saved.'));

            return $this->redirect(['view', 'id' => $model->name]);
        }

        return $this->render('create', ['model' => $model]);
    }

    /**
     * Updates an existing Rule item.
     *
     * If update is successful, the browser will be redirected to the 'view' page.
     *
     * @throws NotFoundHttpException
     */
    public function actionUpdate(string $id): Response|string
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', Yii::t(Module::MODULE_MESSAGES, 'Rule has been saved.'));

            return $this->redirect(['view', 'id' => $model->name]);
        }

        return $this->render('update', ['model' => $model]);
    }

    /**
     * Deletes an existing Rule item.
     *
     * If deletion is successful, the browser will be redirected to the 'index' page.
     *
     * @throws NotFoundHttpException
     */
    public function actionDelete(string $id): Response
    {
        $model = $this->findModel($id);
        Yii::$app->authManager->remove($model->item);
        Yii::$app->session->setFlash('success', Yii::t(Module::MODULE_MESSAGES, 'Rule has been deleted.'));

        return $this->redirect(['index']);
    }

    /**
     * Finds the BizRuleModel based on its primary key value.
     *
     * If the model is not found, a 404 HTTP exception will be thrown.
     *
     * @return BizRuleModel the loaded model
     *
     * @throws NotFoundHttpException
     */
    protected function findModel(string $id): BizRuleModel
    {
        $item = Yii::$app->authManager->getRule($id);

        if (!empty($item)) {
            return new BizRuleModel($item);
        }

        throw new NotFoundHttpException(Yii::t(Module::MODULE_MESSAGES, 'The requested page does not exist.'));
    }
}
