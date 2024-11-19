<?php

namespace admin\controllers;

use common\models\Setting;
use kartik\grid\EditableColumnAction;
use Yii;
use yii\base\InvalidConfigException;
use yii\data\ActiveDataProvider;
use yii\db\Exception;
use yii\filters\VerbFilter;
use yii\helpers\{ArrayHelper, StringHelper};
use yii\web\NotFoundHttpException;

/**
 * Контроллер для доступа к настройкам
 *
 * @package controllers
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
final class SettingController extends AdminController
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
                    'change' => ['POST'],
                    'change-parameter' => ['POST']
                ]
            ]
        ]);
    }

    public function actionIndex(): string
    {
        $dataProvider = new ActiveDataProvider(['query' => Setting::find()]);
        $dataProvider->pagination = false;

        return $this->render('index', ['dataProvider' => $dataProvider]);
    }

    /**
     * Обработка Ajax запроса виджета на изменение параметра
     *
     * @throws InvalidConfigException
     * @throws NotFoundHttpException
     * @throws Exception
     */
    public function actionChangeParameter(): string
    {
        if (Yii::$app->request->isAjax) {
            $data = Yii::$app->request->getBodyParams();
            /** @var Setting $param */
            $param = Setting::find()->where(['parameter' => $data['param']])->one();
            $param->value = $data['value'];
            if ($param->save()) {
                return "Параметр $param->parameter изменен на значение $param->value";
            }
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
                'modelClass' => Setting::class,
                'outputValue' => static function (Setting $model, string $attribute) {
                    if (($attribute === 'value')) {
                        return StringHelper::truncate($model->columnValue, 62);
                    }
                    return $model->$attribute;
                }
            ]
        ];
    }
}
