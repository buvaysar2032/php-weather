<?php

namespace common\modules\mail\controllers;

use admin\controllers\AdminController;
use common\components\helpers\UserUrl;
use common\modules\mail\{enums\LogStatus, models\MailingLog, models\MailingLogSearch};
use Exception;
use kartik\grid\EditableColumnAction;
use Throwable;
use Yii;
use yii\base\InvalidConfigException;
use yii\db\StaleObjectException;
use yii\filters\VerbFilter;
use yii\helpers\{ArrayHelper, Json};
use yii\web\{NotFoundHttpException, Response};

/**
 * MailingLogController implements the CRUD actions for MailingLog model.
 *
 * @package mail\controllers
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
final class MailingLogController extends AdminController
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
     * Lists all MailingLog models.
     *
     * @throws InvalidConfigException
     */
    public function actionIndex(): string
    {
        $searchModel = new MailingLogSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->sort->defaultOrder = ['date' => SORT_DESC];
        return $this->render('index', ['searchModel' => $searchModel, 'dataProvider' => $dataProvider]);
    }

    /**
     * Lists all MailingLog models with errors.
     *
     * @throws InvalidConfigException
     */
    public function actionErrors(): string
    {
        $searchModel = new MailingLogSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams, true);

        return $this->render('errors', ['searchModel' => $searchModel, 'dataProvider' => $dataProvider]);
    }

    /**
     * FAQ
     */
    public function actionFaq(): string
    {
        return $this->render('faq');
    }

    /**
     * Displays a single MailingLog model.
     *
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView(int $id): string
    {
        return $this->render('view', ['model' => $this->findModel($id), 'breadcrumbs' => true]);
    }

    /**
     * Finds the MailingLog model based on its primary key value.
     *
     * If the model is not found, a 404 HTTP exception will be thrown.
     *
     * @throws NotFoundHttpException if the model cannot be found
     */
    private function findModel(int $id): MailingLog
    {
        if (($model = MailingLog::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    /**
     * Повторение отправки
     *
     * @throws NotFoundHttpException
     */
    public function actionRepeat(int $id): Response
    {
        $model = $this->findModel($id);
        $message = Yii::$app->mailer
            ->compose(
                $model->template,
                $model->data ? Json::decode($model->data) : [],
                explode(', ', $model->mail_to)
            )
            ->setSubject($model->mailing_subject);
        $message->previousLogId = $model->id;
        $res = $message->sendAsync();
        if ($res === true) {
            Yii::$app->session->setFlash('success', Yii::t('modules/mail/success', 'Message have been send'));
        } elseif ($res !== false) {
            Yii::$app->session->setFlash('info',
                Yii::t('modules/mail/success', 'Message have been added to send queue'));
        } else {
            Yii::$app->session->setFlash('error', Yii::t('modules/mail/error', 'Message send error'));
        }
        return $this->redirect(UserUrl::setFilters(MailingLogSearch::class));
    }

    /**
     * Повторение всех отправок с ошибками
     */
    public function actionRepeatAll(): Response
    {
        /** @var MailingLog[] $models */
        $models = MailingLog::find()->where(['status' => LogStatus::Error->value])->all();
        try {
            $count = 0;
            foreach ($models as $model) {
                $message = Yii::$app->mailer->compose(
                    $model->template,
                    $model->data ? Json::decode($model->data) : [],
                    $model->mail_to,
                )->setSubject($model->mailing_subject);
                $message->previousLogId = $model->id;
                $message->sendAsync();
                $count++;
            }
            if ($count > 0) {
                Yii::$app->session->setFlash(
                    'success',
                    Yii::t('modules/mail/success', 'Messages have been added to send queue')
                );
            } else {
                Yii::$app->session->setFlash('error', Yii::t('modules/mail/error', 'Repeatable error logs not found'));
            }
        } catch (Exception $e) {
            Yii::$app->session->setFlash('error', $e->getMessage());
        }
        return $this->redirect(UserUrl::setFilters(MailingLogSearch::class));
    }

    /**
     * Deletes an existing MailingLog model.
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
        Yii::$app->session->setFlash('success', "Лог №$id удален успешно");
        return $this->redirect(UserUrl::setFilters(MailingLogSearch::class));
    }

    /**
     * {@inheritdoc}
     */
    public function actions(): array
    {
        return [
            'change' => [
                'class' => EditableColumnAction::class,
                'modelClass' => MailingLog::class
            ]
        ];
    }
}
