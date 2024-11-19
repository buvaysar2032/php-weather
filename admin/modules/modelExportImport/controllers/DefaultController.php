<?php

namespace admin\modules\modelExportImport\controllers;

use admin\controllers\AdminController;
use admin\modules\modelExportImport\behaviors\ExportImportBehavior;
use common\components\helpers\UserFileHelper;
use common\models\Setting;
use Exception;
use Yii;
use yii\base\InvalidConfigException;
use yii\db\BaseActiveRecord;
use yii\helpers\Json;
use yii\httpclient\{Client, Exception as HttpClientException};
use yii\web\{NotFoundHttpException, Response};

/**
 * Class DefaultController
 *
 * @package modelExportImport\controllers
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
final class DefaultController extends AdminController
{
    /**
     * @throws NotFoundHttpException
     */
    public function actionIndex(): string
    {
        $remoteUrl = Setting::getParameterValue('remote_import_url');
        return $this->render('index', ['remoteUrl' => $remoteUrl]);
    }

    /**
     * @throws InvalidConfigException
     * @throws HttpClientException
     * @throws NotFoundHttpException
     * @throws Exception
     */
    public function actionExportToRemote(int $index = null): Response
    {
        set_time_limit(600);
        /** @var BaseActiveRecord[]|string[] $modelsList */
        $modelsList = [
            // Заполнить именами классов экспортируемых моделей проекта
        ];
        $remoteUrl = Setting::getParameterValue('remote_import_url');
        $data = $deletedData = [];
        $time = (int)UserFileHelper::getDataFromFile('lastExportTime' . $index);
        if ($index !== null) {
            $item = $modelsList[$index];
            $this->_getModelsData($item, $time, $data, $deletedData);
        } else {
            foreach ($modelsList as $item) {
                $this->_getModelsData($item, $time, $data, $deletedData);
            }
        }
        $data = array_merge($data, ...$deletedData);
        $client = new Client();
        $request = $client->createRequest();
        $response = $request->setUrl($remoteUrl)
            ->setOptions(['timeout' => 1200])
            ->setMethod('POST')
            ->setData(['data' => Json::encode($data)])
            ->send();
        if (!$response->isOk) {
            Yii::$app->session->setFlash('error', 'Ошибка переноса данных: ' . print_r($response->data, true));
        } else {
            UserFileHelper::saveDataToFile((string)time(), 'lastExportTime' . $index);
            Yii::$app->session->setFlash('success', 'Данные успешно перенесены');
        }
        return $this->redirect(['index']);
    }

    /**
     * @throws Exception
     */
    private function _getModelsData(string|BaseActiveRecord|ExportImportBehavior $item, int $time, array &$data, array &$deletedData): void
    {
        $query = $item::find();
        if ($time) {
            $query->where(['>=', $item->updatedAtAttribute, $time]);
        }
        /** @var BaseActiveRecord[]|ExportImportBehavior[] $models */
        $models = $query->all();
        foreach ($models as $model) {
            $data[] = $model->export();
            $deletedData[] = $model->getDeletedModels($time ?: null);
        }
    }
}
