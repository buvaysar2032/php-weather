<?php

namespace admin\modules\modelExportImport\controllers;

use admin\controllers\AdminController;
use admin\modules\modelExportImport\models\{ModelImportLog, ModelImportLogSearch};
use common\components\{exceptions\ModelSaveException, helpers\UserUrl};
use Throwable;
use Yii;
use yii\base\InvalidConfigException;
use yii\db\StaleObjectException;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\{NotFoundHttpException, Response};

/**
 * ModelImportLogController implements the CRUD actions for ModelImportLog model.
 *
 * @package modelExportImport\controllers
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
final class ModelImportLogController extends AdminController
{
    /**
     * {@inheritdoc}
     */
    public function behaviors(): array
    {
        return ArrayHelper::merge(parent::behaviors(), [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST']
                ]
            ]
        ]);
    }

    /**
     * Lists all ModelImportLog models.
     *
     * @throws InvalidConfigException
     */
    public function actionIndex(): string
    {
        $searchModel = new ModelImportLogSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->sort->defaultOrder = ['imported_at' => SORT_DESC];
        return $this->render('index', ['searchModel' => $searchModel, 'dataProvider' => $dataProvider]);
    }

    /**
     * Displays a single ModelImportLog model.
     *
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView(int $id): string
    {
        return $this->render('view', ['model' => $this->findModel($id)]);
    }

    /**
     * Возвращение состояния модели из лога в исходное до импорта состояние
     *
     * @throws Throwable
     * @throws ModelSaveException
     * @throws StaleObjectException
     * @throws NotFoundHttpException
     */
    public function actionReverse(int $id): Response
    {
        $this->findModel($id)->reverseModel();
        Yii::$app->session->setFlash('success', 'Импорт отменен');
        return $this->redirect(Yii::$app->request->referrer);
    }

    /**
     * Deletes an existing ModelImportLog model.
     *
     * If deletion is successful, the browser will be redirected to the 'index' page.
     *
     * @throws Throwable
     * @throws StaleObjectException
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete(int $id): Response
    {
        $this->findModel($id)->delete();
        Yii::$app->session->setFlash('success', "Лог импорта №$id удален успешно");
        return $this->redirect(UserUrl::setFilters(ModelImportLogSearch::class));
    }

    /**
     * Finds the ModelImportLog model based on its primary key value.
     *
     * If the model is not found, a 404 HTTP exception will be thrown.
     *
     * @throws NotFoundHttpException if the model cannot be found
     */
    private function findModel(int $id): ModelImportLog
    {
        if (($model = ModelImportLog::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
    }
}
