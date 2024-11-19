<?php

namespace common\modules\user\controllers;

use admin\components\actions\ListSearchAction;
use admin\controllers\AdminController;
use common\components\helpers\UserUrl;
use common\modules\user\{enums\Status, models\User, models\UserSearch, Module};
use Exception;
use kartik\grid\EditableColumnAction;
use Throwable;
use Yii;
use yii\base\InvalidConfigException;
use yii\db\StaleObjectException;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\{NotFoundHttpException, Response};

/**
 * UserController implements the CRUD actions for User model.
 *
 * @package user\controllers
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
final class UserController extends AdminController
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
     * Lists all User models.
     *
     * @throws InvalidConfigException
     */
    public function actionIndex(): string
    {
        $searchModel = new UserSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        return $this->render('index', ['searchModel' => $searchModel, 'dataProvider' => $dataProvider]);
    }

    /**
     * Displays a single User model.
     *
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView(int $id): string
    {
        return $this->render('view', ['model' => $this->findModel($id)]);
    }

    /**
     * Finds the User model based on its primary key value.
     *
     * If the model is not found, a 404 HTTP exception will be thrown.
     *
     * @throws NotFoundHttpException if the model cannot be found
     */
    private function findModel(int $id): User
    {
        if (($model = User::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
    }

    /**
     * Отправка письма подтверждения
     *
     * @throws NotFoundHttpException
     */
    public function actionMail(int $id): Response
    {
        $user = $this->findModel($id);
        if ($user->email) {
            if ($user->email->is_confirmed) {
                Yii::$app->session->setFlash('success', Yii::t(Module::MODULE_MESSAGES, 'Email already confirmed'));
            } else {
                try {
                    $user->email->sendVerificationEmail();
                    Yii::$app->session->setFlash(
                        'success',
                        Yii::t(Module::MODULE_SUCCESS_MESSAGES, 'Message have been send')
                    );
                } catch (Exception $exception) {
                    Yii::$app->session->setFlash('error', $exception->getMessage());
                }
            }
        }
        return $this->redirect(UserUrl::setFilters(UserSearch::class));
    }

    /**
     * Deletes an existing User model.
     *
     * If deletion is successful, the browser will be redirected to the 'index' page.
     *
     * @throws Throwable
     * @throws StaleObjectException
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete(int $id): Response
    {
        $user = $this->findModel($id);
        $user->delete();
        Yii::$app->session->setFlash('success', "Пользователь №$id удален успешно");
        return $this->redirect(UserUrl::setFilters(UserSearch::class));
    }

    /**
     * {@inheritdoc}
     */
    public function actions(): array
    {
        return [
            'change' => [
                'class' => EditableColumnAction::class,
                'modelClass' => User::class,
                'outputValue' => static function (User $model, string $attribute) {
                    if ($attribute === 'status') {
                        return Status::from($model->$attribute)->coloredDescription();
                    }
                    return $model->$attribute;
                }
            ],
            'list' => ['class' => ListSearchAction::class]
        ];
    }

}
