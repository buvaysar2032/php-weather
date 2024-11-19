<?php

namespace admin\controllers;

use admin\enums\AdminStatus;
use admin\modules\rbac\components\RbacHtml;
use admin\models\{AdminSignupForm, PasswordChangeForm, UserAdmin, UserAdminSearch};
use common\components\helpers\UserUrl;
use kartik\grid\EditableColumnAction;
use Throwable;
use Yii;
use yii\base\{Exception, InvalidConfigException};
use yii\db\StaleObjectException;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\{NotFoundHttpException, Response};

/**
 * UserAdminController implements the CRUD actions for UserAdmin model.
 *
 * @package controllers
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
final class UserAdminController extends AdminController
{
    /**
     * {@inheritdoc}
     */
    public function behaviors(): array
    {
        return ArrayHelper::merge(parent::behaviors(), [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => ['delete' => ['POST']]
            ]
        ]);
    }

    /**
     * Lists all UserAdmin models.
     *
     * @throws Exception
     * @throws InvalidConfigException
     * @throws \Exception
     */
    public function actionIndex(): string
    {
        $searchModel = new UserAdminSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $model = new AdminSignupForm();
        if (RbacHtml::isAvailable(['admin-signup']) && $model->load(Yii::$app->request->post()) && $model->signup()) {
            Yii::$app->session->setFlash('success', 'Администратор создан успешно');
        }

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'model' => $model
        ]);
    }

    /**
     * Экшен для загрузки формы без модального окна, необходим для определения в модуле rbac-admin
     */
    public function actionAdminSignup(): Response
    {
        return $this->redirect(UserUrl::setFilters(UserAdminSearch::class));
    }

    /**
     * Displays a single UserAdmin model.
     *
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView(int $id): string
    {
        return $this->render('view', ['model' => $this->findModel($id)]);
    }

    /**
     * Finds the UserAdmin model based on its primary key value.
     *
     * If the model is not found, a 404 HTTP exception will be thrown.
     *
     * @throws NotFoundHttpException if the model cannot be found
     */
    private function findModel(int $id): UserAdmin
    {
        if (($model = UserAdmin::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
    }

    /**
     * Updates an existing UserAdmin model.
     *
     * If update is successful, the browser will be redirected to the 'view' page.
     *
     * @throws NotFoundHttpException if the model cannot be found
     * @throws InvalidConfigException
     */
    public function actionUpdate(int $id): Response|string
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', "Данные администратора №$id изменены успешно");
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', ['model' => $model]);
    }

    /**
     * Deletes an existing UserAdmin model.
     *
     * If deletion is successful, the browser will be redirected to the 'index' page.
     *
     * @throws Throwable
     * @throws StaleObjectException
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete(int $id): Response
    {
        $model = $this->findModel($id);
        if ($model->id === (int)Yii::$app->user->identity->getId()) {
            Yii::$app->session->setFlash('error', Yii::t('app/error', 'Can not delete self profile'));
            return $this->redirect(Yii::$app->request->referrer);
        }
        if (UserAdmin::find()->count() <= 1) {
            Yii::$app->session->setFlash('error', Yii::t('app/error', 'Last active administrator!'));
            return $this->redirect(Yii::$app->request->referrer);
        }
        $model->delete();
        Yii::$app->session->setFlash('success', "Администратор №$id удален успешно");
        return $this->redirect(UserUrl::setFilters(UserAdminSearch::class));
    }

    /**
     * @throws NotFoundHttpException
     * @throws Exception
     */
    public function actionPasswordChange(int $id): Response|string
    {
        $admin = $this->findModel($id);
        $admin->scenario = UserAdmin::SCENARIO_UPDATE_PASSWORD;
        $model = new PasswordChangeForm($admin);

        if ($model->load(Yii::$app->request->post()) && $model->changePassword()) {
            Yii::$app->session->setFlash('success', 'Пароль успешно изменен');
            return $this->redirect(UserUrl::setFilters(UserAdminSearch::class));
        }
        return $this->render('change-pass', compact('model'));
    }

    /**
     * {@inheritdoc}
     */
    public function actions(): array
    {
        return [
            'change' => [
                'class' => EditableColumnAction::class,
                'modelClass' => UserAdmin::class,
                'outputValue' => static function (UserAdmin $model, string $attribute) {
                    if ($attribute === 'status') {
                        return AdminStatus::from($model->status)->coloredDescription();
                    }
                    return $model->$attribute;
                }
            ]
        ];
    }
}
