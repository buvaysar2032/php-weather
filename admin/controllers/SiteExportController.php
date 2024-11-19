<?php

namespace admin\controllers;

use admin\components\auth\AdminAccessControl;
use admin\models\SiteExportForm;
use Exception;
use Yii;
use yii\helpers\ArrayHelper;
use yii\web\{NotFoundHttpException, Response};

/**
 * Class SiteExportController
 *
 * @package controllers
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
final class SiteExportController extends AdminController
{
    /**
     * @throws NotFoundHttpException
     * @throws Exception
     */
    public function actionIndex(string $token = null): Response|string
    {
        set_time_limit(0);
        //Защита от доступа с помощью ссылки, зависящей от домена
        if (
            $token === null ||
            $token !== md5(Yii::$app->request->hostInfo . Yii::$app->request->cookieValidationKey)
        ) {
            throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
        }
        $model = new SiteExportForm();
        if ($model->load(Yii::$app->request->post()) && $model->exportSiteProject()) {
            return Yii::$app->response->sendFile($model->filename);
        }
        return $this->render('index', ['model' => $model]);
    }

    /**
     * @throws NotFoundHttpException
     * @throws Exception
     */
    public function actionDownload(string $token = null): Response
    {
        //Защита от доступа с помощью ссылки, зависящей от домена
        if (
            $token === null ||
            $token !== md5(Yii::$app->request->hostInfo . Yii::$app->request->cookieValidationKey)
        ) {
            throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
        }
        $model = new SiteExportForm();
        if (file_exists($model->filename)) {
            return Yii::$app->response->sendFile($model->filename);
        }
        throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
    }
}
