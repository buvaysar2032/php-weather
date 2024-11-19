<?php

namespace admin\controllers;

use admin\modules\rbac\components\RbacHtml;
use common\components\helpers\UserUrl;
use common\enums\ParamType;
use common\models\{Param, ParamSearch};
use kartik\grid\EditableColumnAction;
use Throwable;
use Yii;
use yii\base\InvalidConfigException;
use yii\db\{Exception, StaleObjectException};
use yii\filters\VerbFilter;
use yii\helpers\{ArrayHelper, StringHelper};
use yii\web\{NotFoundHttpException, Response};

/**
 * ParamController implements the CRUD actions for Param model.
 *
 * @package admin\controllers
 */
final class ParamController extends AdminController
{
    /**
     * {@inheritdoc}
     */
    public function behaviors(): array
    {
        return ArrayHelper::merge(parent::behaviors(), [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => ['delete' => ['POST']],
            ],
        ]);
    }

    /**
     * Lists all Param models.
     *
     * @throws InvalidConfigException
     * @throws \Exception
     */
    public function actionIndex(): string
    {
        $model = new Param();
        $model->deletable = 1;
        $model->type = ParamType::Text->value;
        if (RbacHtml::isAvailable(['create']) && $model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', "Элемент №$model->id создан успешно");
        }

        $searchModel = new ParamSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->pagination = false;

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'model' => $model,
        ]);
    }

    /**
     * Creates a new Param model.
     *
     * If creation is successful, the browser will be redirected to the 'view' page.
     *
     * @param string|null $redirect если нужен иной редирект после успешного создания
     *
     * @throws InvalidConfigException
     * @throws Exception
     */
    public function actionCreate(string $redirect = null): Response|string
    {
        $model = new Param();
        $model->deletable = 1;
        $model->type = ParamType::Text->value;
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', "Элемент №$model->id создан успешно");
            return match ($redirect) {
                'create' => $this->redirect(['create']),
                'index' => $this->redirect(UserUrl::setFilters(ParamSearch::class)),
                default => $this->redirect(['view', 'id' => $model->id])
            };
        }

        return $this->render('create', ['model' => $model]);
    }

    /**
     * Deletes an existing Param model.
     *
     * If deletion is successful, the browser will be redirected to the 'index' page.
     *
     * @throws NotFoundHttpException if the model cannot be found
     * @throws StaleObjectException
     * @throws Throwable
     */
    public function actionDelete(int $id): Response
    {
        $model = $this->findModel($id);
        if ($model->deletable) {
            $model->delete();
            Yii::$app->session->setFlash('success', "Элемент №$id удален успешно");
        }
        return $this->redirect(UserUrl::setFilters(ParamSearch::class));
    }

    /**
     * Finds the Param model based on its primary key value.
     *
     * If the model is not found, a 404 HTTP exception will be thrown.
     *
     * @throws NotFoundHttpException if the model cannot be found
     */
    private function findModel(int $id): Param
    {
        if (($model = Param::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
    }

    /**
     * {@inheritdoc}
     */
    public function actions(): array
    {
        return [
            'change' => [
                'class' => EditableColumnAction::class,
                'modelClass' => Param::class,
                'outputValue' => static function (Param $model, string $attribute) {
                    if ($attribute === 'value') {
                        if ($model->type === ParamType::Text->value) {
                            return StringHelper::truncate($model->columnValue, 62);
                        }
                        return $model->columnValue;
                    }
                    return $model->$attribute;
                },
            ],
        ];
    }
}
