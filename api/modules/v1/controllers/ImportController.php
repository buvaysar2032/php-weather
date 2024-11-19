<?php

namespace api\modules\v1\controllers;

use admin\modules\modelExportImport\behaviors\ExportImportBehavior;
use common\components\exceptions\ModelSaveException;
use common\models\Setting;
use Throwable;
use Yii;
use yii\base\InvalidConfigException;
use yii\db\{ActiveRecord, StaleObjectException};
use yii\helpers\{ArrayHelper, Json};
use yii\web\NotFoundHttpException;

/**
 * Class ImportController
 *
 * @package controllers
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
final class ImportController extends AppController
{
    /**
     * {@inheritdoc}
     */
    public function behaviors(): array
    {
        return ArrayHelper::merge(parent::behaviors(), ['auth' => ['except' => ['index']]]);
    }

    /**
     * @param string $token Токен доступа к методу
     *
     * @throws Throwable
     * @throws ModelSaveException
     * @throws InvalidConfigException
     * @throws StaleObjectException
     * @throws NotFoundHttpException
     */
    public function actionIndex(string $token): array
    {
        if ($token !== Setting::getParameterValue('import_api_token')) {
            return $this->returnErrorBadRequest();
        }
        set_time_limit(0);
        $data = Json::decode(Yii::$app->request->post('data'));
        if (is_array($data)) {
            foreach ($data as $item) {
                if (
                    isset($item[ExportImportBehavior::CLASS_PARAM]) &&
                    class_exists($item[ExportImportBehavior::CLASS_PARAM])
                ) {
                    /* @var ActiveRecord|ExportImportBehavior $model */
                    $model = new $item[ExportImportBehavior::CLASS_PARAM]();
                    if ($model instanceof ActiveRecord) {
                        $model->import($item);
                        continue;
                    }
                }
                return $this->returnError("Неизвестная модель данных `{$item[ExportImportBehavior::CLASS_PARAM]}`");
            }
        }
        return $this->returnSuccess('Данные импортированы');
    }
}
