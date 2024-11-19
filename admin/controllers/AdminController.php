<?php

namespace admin\controllers;

use common\components\helpers\ModuleHelper;
use Yii;
use yii\bootstrap5\BootstrapAsset;
use yii\filters\AccessControl;
use yii\web\{BadRequestHttpException, Controller, NotFoundHttpException};

/**
 * Базовый контроллер панели администратора, ограничивает доступ неавторизованным пользователям
 *
 * @package controllers
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
abstract class AdminController extends Controller
{
    /**
     * {@inheritdoc}
     * @throws BadRequestHttpException
     * @throws NotFoundHttpException
     */
    public function beforeAction($action): bool
    {
        if (!ModuleHelper::isAdminModule()) {
            throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
        }
        $res = parent::beforeAction($action);
        if (Yii::$app->themeManager->isDark) {
            Yii::$app->assetManager->bundles[BootstrapAsset::class] = [
                'sourcePath' => '@bower/bootswatch/dist',
                'css' => ['darkly/bootstrap.css']
            ];
        }
        return $res;
    }
}
