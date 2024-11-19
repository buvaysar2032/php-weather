<?php

namespace common\modules\notification\controllers;

use common\components\exceptions\ModelSaveException;
use common\enums\Boolean;
use common\modules\notification\models\Notification;
use Throwable;
use Yii;
use yii\db\StaleObjectException;
use yii\web\{BadRequestHttpException, Controller, Response};

/**
 * Default controller for the `notification` module
 *
 * @package notification
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
final class DefaultController extends Controller
{
    /**
     * {@inheritdoc}
     * @throws BadRequestHttpException
     */
    public function beforeAction($action): bool
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        return parent::beforeAction($action);
    }

    /**
     * Получить список новых уведомлений
     */
    public function actionIndex(int $time): array
    {
        return [
            'notifications' => Notification::find()
                ->where(['>=', 'updated_at', $time])
                ->orderBy(['updated_at' => SORT_DESC])
                ->all()
        ];
    }

    /**
     * Отметить уведомление просмотренным
     *
     * @throws ModelSaveException
     */
    public function actionView(int $id): string
    {
        if ($model = Notification::findOne($id)) {
            $model->is_viewed = Boolean::Yes->value;
            if (!$model->save()) {
                throw new ModelSaveException($model);
            }
        }
        return 'OK';
    }

    /**
     * Отметить все уведомления как просмотренные
     *
     * @throws ModelSaveException
     */
    public function actionViewAll(): string
    {
        /** @var Notification[] $models */
        $models = Notification::find()->where(['is_viewed' => Boolean::No->value])->all();
        foreach ($models as $model) {
            $model->is_viewed = Boolean::Yes->value;
            if (!$model->save()) {
                throw new ModelSaveException($model);
            }
        }
        return 'OK';
    }

    /**
     * Закрыть уведомление, т.е. удалить его из БД
     *
     * @throws Throwable
     * @throws StaleObjectException
     */
    public function actionClose(int $id): string
    {
        if ($model = Notification::findOne($id)) {
            $model->delete();
        }
        return 'OK';
    }

    /**
     * Закрыть все уведомления, т.е. очистить таблицу БД
     */
    public function actionCloseAll(): string
    {
        Notification::deleteAll();
        return 'OK';
    }
}
