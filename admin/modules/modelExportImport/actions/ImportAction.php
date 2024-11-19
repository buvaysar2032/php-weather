<?php

namespace admin\modules\modelExportImport\actions;

use admin\modules\modelExportImport\{behaviors\ExportImportBehavior, models\ImportModel};
use common\components\exceptions\ModelSaveException;
use Throwable;
use Yii;
use yii\base\Action;
use yii\db\{ActiveRecord, StaleObjectException};
use yii\helpers\Json;
use yii\web\{Response, UploadedFile};

/**
 * Action для импорта одной модели
 *
 * Внимание, импорт происходит по primaryKey.
 * То есть изменения сделанные на сервере, могут быть утеряны
 *
 * @package modelExportImport\actions
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
final class ImportAction extends Action
{
    /**
     * @throws Throwable
     * @throws ModelSaveException
     * @throws StaleObjectException
     */
    public function run(): Response
    {
        $model = new ImportModel();
        if (!$model->load(Yii::$app->request->post())) {
            Yii::$app->session->setFlash('error', 'Ошибка загрузки файла');
            return $this->controller->redirect(Yii::$app->request->referrer);
        }
        $file = UploadedFile::getInstance($model, 'file');
        if (!$file) {
            Yii::$app->session->setFlash('error', 'Ошибка загрузки файла');
            return $this->controller->redirect(Yii::$app->request->referrer);
        }
        $data = Json::decode(file_get_contents($file->tempName));
        $class = $data[ExportImportBehavior::CLASS_PARAM] ?? null;
        if ($class && class_exists($class)) {
            /* @var ActiveRecord|ExportImportBehavior $model */
            $model = new $class();
            if ($model instanceof ActiveRecord) {
                $model->import($data);
                Yii::$app->session->setFlash('success', 'Данные импортированы');
            } else {
                Yii::$app->session->setFlash('error', 'Неизвестная модель данных');
            }
        }
        return $this->controller->redirect(Yii::$app->request->referrer);
    }
}
