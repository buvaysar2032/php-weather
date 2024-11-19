<?php

namespace admin\components\uploadForm\actions;

use admin\components\parsers\{CSVParser, OdsParser, XlsxParser};
use admin\components\uploadForm\models\{UploadForm, UploadInterface};
use common\components\exceptions\ModelSaveException;
use Yii;
use yii\base\{Action, InvalidConfigException};
use yii\web\{BadRequestHttpException, Response, UploadedFile};

/**
 * Загружает файл из формы.
 *
 * @package admin\components\uploadForm\actions
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
class UploadAction extends Action
{
    /**
     * Имя класса модели с реализованным статическим методом insertFromFile()
     * @see UploadInterface
     */
    public string|UploadInterface $modelClass;

    /**
     * Имя класса модели для загрузки
     */
    public string $uploadModelClass = UploadForm::class;

    /**
     * @throws BadRequestHttpException|ModelSaveException|InvalidConfigException
     */
    final public function run(): Response
    {
        $model = new $this->uploadModelClass();
        if (Yii::$app->request->isPost) {
            $model->load(Yii::$app->request->post());
            $model->file = UploadedFile::getInstance($model, 'file');
            if (!$model->validate()) {
                throw new ModelSaveException($model);
            }
            $parser = match ($model->file->extension) {
                'csv' => new CSVParser(),
                'ods' => new OdsParser(),
                'xlsx' => new XlsxParser(),
                default => throw new InvalidConfigException(
                    'Не найден парсер для формата - ' . $model->file->extension
                ),
            };

            //В модели нужно реализовать статический метод insertFromFile()
            $this->modelClass::insertFromFile($model, $parser);
            return $this->controller->redirect(['index']);
        }
        throw new BadRequestHttpException();
    }
}
