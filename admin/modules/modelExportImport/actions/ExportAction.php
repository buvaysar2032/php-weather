<?php

namespace admin\modules\modelExportImport\actions;

use admin\modules\modelExportImport\behaviors\ExportImportBehavior;
use Exception;
use Yii;
use yii\base\{Action, InvalidConfigException};
use yii\db\ActiveRecord;
use yii\helpers\Json;
use yii\web\{NotFoundHttpException, RangeNotSatisfiableHttpException, Response};

/**
 * Action для экспорта одной модели в json формате
 *
 * @package modelExportImport\actions
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
final class ExportAction extends Action
{
    /**
     * @var ActiveRecord|string
     */
    public ActiveRecord|string $modelClass;

    /**
     * {@inheritdoc}
     * @throws InvalidConfigException
     */
    public function init(): void
    {
        if (!isset($this->modelClass)) {
            throw new InvalidConfigException('`className` must be defined');
        }
        parent::init();
    }

    /**
     * @throws RangeNotSatisfiableHttpException
     * @throws Exception
     */
    public function run(): Response
    {
        $query = $this->modelClass::find();
        foreach ($this->modelClass::primaryKey() as $key) {
            $query->andWhere([$key => Yii::$app->request->get($key)]);
        }
        /** @var ActiveRecord|ExportImportBehavior|null $model */
        if (!$model = $query->one()) {
            throw new NotFoundHttpException('The requested model was not found');
        }
        $data = Json::encode($model->export());
        return Yii::$app->response->sendContentAsFile(
            $data,
            basename(str_replace('\\', '/', $model::class)) . '-' .
            (implode('-', $model->getPrimaryKey(true))) . '-' . date('d-m-y') . '.json'
        );
    }
}
