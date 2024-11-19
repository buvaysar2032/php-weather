<?php

namespace admin\modules\modelExportImport\behaviors;

use common\models\traits\ObjectStorageTrait;
use admin\modules\modelExportImport\models\{DeletedModel, ModelImportLog};
use common\components\{exceptions\ModelSaveException, helpers\UserFileHelper};
use Exception;
use Throwable;
use Yii;
use yii\base\{Behavior, InvalidConfigException};
use yii\db\{BaseActiveRecord, StaleObjectException};
use yii\helpers\ArrayHelper;

/**
 * Class ExportImportBehavior
 *
 * @package modelExportImport
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 *
 * @property BaseActiveRecord $owner
 */
class ExportImportBehavior extends Behavior
{
    use ObjectStorageTrait;

    /**
     * Параметр с именем класса
     */
    public const CLASS_PARAM = '_class_';

    /**
     * Параметр флага удаления
     */
    public const IS_DELETED_PARAM = '_is_deleted_';

    /**
     * Параметр времени удаления
     */
    public const DELETED_AT_PARAM = '_deleted_at_';

    /**
     * List of related models, every one of them must have same behavior for recursive export/import
     */
    public array $relations = [];

    /**
     * List of field with not indexed images
     */
    public array $imageLinkFields = [];

    /**
     * Поле по которому определяется уникальность сущности (необходимость создавать с нуля)
     */
    public string $uniqueField;

    /**
     * Атрибут модели для даты изменения
     */
    public string $updatedAtAttribute;

    /**
     * Веб рут каталог сайта
     */
    private string $_webroot;

    /**
     * Доменное имя сайта
     */
    private string $_domain;

    /**
     * {@inheritdoc}
     * @throws InvalidConfigException
     * @throws Exception
     */
    final public function init(): void
    {
        if (isset($this->uniqueField) && (!isset($this->updatedAtAttribute))) {
            throw new InvalidConfigException(
                'If model is unique `updatedAtAttribute` must be defined'
            );
        }
        $this->_webroot = Yii::getAlias('@root/htdocs');
        $this->_domain = Yii::$app->request->hostInfo;
    }

    /**
     * {@inheritdoc}
     */
    final public function events(): array
    {
        return ArrayHelper::merge(parent::events(), [
            BaseActiveRecord::EVENT_AFTER_DELETE => 'addDeletedModel'
        ]);
    }

    /**
     * Логирование удаленных записей
     *
     * @throws ModelSaveException
     */
    final public function addDeletedModel(): void
    {
        $model = $this->owner;
        $deletedModel = new DeletedModel();
        $deletedModel->model_class = $model::class;
        $deletedModel->unique_field = $model->{$this->uniqueField};
        if (!$deletedModel->save()) {
            throw new ModelSaveException($deletedModel);
        }
    }

    /**
     * Получить список удаленных сущностей от какого то времени
     */
    final public function getDeletedModels(int $timeFrom = null): array
    {
        if (!isset($this->uniqueField)) {
            return [];
        }
        $model = $this->owner;
        /* @var BaseActiveRecord|string $class */
        $class = $model::class;
        $query = DeletedModel::find()->where(['modelClass' => $class]);
        if ($timeFrom !== null) {
            $query->andWhere(['>=', 'deleted_at', $timeFrom]);
        }
        /* @var DeletedModel[] $deletedModels */
        $deletedModels = $query->asArray()->all();
        $resData = [];
        foreach ($deletedModels as $deletedModel) {
            $resData[] = [
                self::CLASS_PARAM => $deletedModel->model_class,
                $this->uniqueField => $deletedModel->unique_field,
                self::IS_DELETED_PARAM => true,
                self::DELETED_AT_PARAM => $deletedModel->deleted_at
            ];
        }
        return $resData;
    }

    /**
     * Экспорт
     */
    final public function export(): ?array
    {
        $model = $this->owner;
        /* @var BaseActiveRecord|string $class */
        $class = $model::class;
        // Получение исходных данных
        $mainItem = [];
        foreach ($model->attributes() as $attribute) {
            $mainItem[$attribute] = $model->$attribute;
        }
        // Помечаем от какого класса дамп данных
        $mainItem['_class_'] = $class;

        // Создание абсолютных ссылок на загруженный контент, который не индексируется
        foreach ($this->imageLinkFields as $imageLinkField) {
            if (is_file($this->_webroot . urldecode($mainItem[$imageLinkField]))) {
                $mainItem[$imageLinkField] = $this->_domain . $mainItem[$imageLinkField];
            } elseif (self::hasS3Storage()){
                try {
                    $objectName = preg_replace('/^\/uploads\//', '', $mainItem[$imageLinkField]);
                    if (self::getS3Client()->doesObjectExist(Yii::$app->environment->S3_BUCKET, $objectName)) {
                        $mainItem[$imageLinkField] = $this->_domain . $mainItem[$imageLinkField];
                    }
                } catch (Exception $exception) {
                    Yii::error($exception->getMessage(), __METHOD__);
                }
            }
        }

        // Сбор данных из связей "вниз", не использовать связи в модели, т.к. они могут быть не объявлены явно, либо не иметь внешних ключей в БД
        /* @var BaseActiveRecord|string $relatedModel */
        foreach ($this->relations as $relatedModel => $relation) {
            /** @var BaseActiveRecord[]|ExportImportBehavior[] $subModels */
            $subModels = $relatedModel::find()->where([$relation[0] => $mainItem[$relation[1]]])->all();
            $mainItem[$relatedModel] = [];
            foreach ($subModels as $subModel) {
                $mainItem[$relatedModel][] = $subModel->export();
            }
        }

        return $mainItem;
    }

    /**
     * Импорт уникального json-а
     *
     * @throws Throwable
     * @throws ModelSaveException
     * @throws InvalidConfigException
     * @throws StaleObjectException
     */
    final public function import(array $data, bool $createLog = true): void
    {
        /** @var BaseActiveRecord|string $class */
        $class = $this->owner::class;
        // Даем дополнительное время на возможное скачивание данных
        if (isset($this->uniqueField)) {
            $uniqueHash = $data[$this->uniqueField];
            /** @var BaseActiveRecord|self $model */
            if (!$model = $class::findOne([$this->uniqueField => $uniqueHash])) {
                // Если модель была удалена и удаление актуальней дампа, то пропускаем, иначе восстанавливаем
                /* @var DeletedModel $deleted */
                if (
                    ($deleted = DeletedModel::find()
                        ->where(['modelClass' => $class, 'uniqueField' => $uniqueHash])
                        ->one())
                    && $deleted->deleted_at > $data[$this->updatedAtAttribute]
                ) {
                    return;
                }
                $model = $this->owner;
            }
        } else {
            $model = $this->owner;
        }

        // Если сущность надо удалить и время удаления актуальней текущей версии
        if (array_key_exists(self::IS_DELETED_PARAM, $data) && $data[self::IS_DELETED_PARAM]) {
            $this->_deleteModel($data, $model, $class, $createLog);
            return;
        }

        // Если сущность не уникальна либо новее, то импортируем данные
        if (!isset($this->uniqueField) || ($model->{$this->updatedAtAttribute} <= $data[$this->updatedAtAttribute])) {
            $this->_importModel($data, $model, $class, $createLog);
        }

        // Рекурсивно импортируем связанные модели
        foreach ($this->relations as $relatedModel => $relation) {
            $this->_importRelation($relation, $model, $relatedModel, $data[$relatedModel]);
        }
    }

    /**
     * Удалить модель, если она уже неактуальна по данным импорта
     *
     * @param array                 $data      Данные импорта
     * @param BaseActiveRecord|self $model     Модель-владелец поведения
     * @param string                $class     Название класса
     * @param bool                  $createLog Создавать ли лог
     *
     * @throws Throwable
     * @throws ModelSaveException
     * @throws StaleObjectException
     */
    private function _deleteModel(
        array $data,
        ExportImportBehavior|BaseActiveRecord $model,
        string $class,
        bool $createLog
    ): void {
        // Если на бою актуальней, то не удаляем, а просто пропускаем импорт (данных тут все равно нет)
        if (
            array_key_exists(self::DELETED_AT_PARAM, $data) &&
            ((int)$data[self::DELETED_AT_PARAM]) >= $model->{$this->updatedAtAttribute}
        ) {
            if (isset($this->uniqueField) && $model->{$this->uniqueField} && $createLog) {
                ModelImportLog::add($class, $this->uniqueField, $model->{$this->uniqueField}, $model->export(), $data);
            }
            $model->delete();
        }
    }

    /**
     * Импорт данных модели
     *
     * @param array                 $data      Данные импорта
     * @param BaseActiveRecord|self $model     Модель-владелец поведения
     * @param string                $class     Название класса
     * @param bool                  $createLog Создавать ли лог
     *
     * @throws ModelSaveException
     * @throws InvalidConfigException
     * @throws Exception
     */
    private function _importModel(
        array $data,
        ExportImportBehavior|BaseActiveRecord $model,
        string $class,
        bool $createLog
    ): void {
        if (isset($this->uniqueField) && $model->{$this->uniqueField} && $createLog) {
            ModelImportLog::add($class, $this->uniqueField, $model->{$this->uniqueField}, $model->export(), $data);
        }
        $model->load($data, '');
        // Скачиваем изображения
        foreach ($this->imageLinkFields as $imageLinkField) {
            $url = $data[$imageLinkField];
            if ($url) {
                $dest = preg_replace('/https?:\/\/.*?\//', '/', $url);
                UserFileHelper::createDirectory(str_replace(basename($dest), '', $this->_webroot . $dest));
                if (copy($url, $this->_webroot . $dest)) {
                    $model->$imageLinkField = $dest;
                    if (self::hasS3Storage()) {
                        $objectName = preg_replace('/^\/uploads\//', '', $dest);
                        try {
                            self::getS3Client()
                                ->upload(
                                    Yii::$app->environment->S3_BUCKET,
                                    $objectName,
                                    fopen($this->_webroot . $dest, 'r')
                                );
                        } catch (Exception $exception) {
                            Yii::error($exception->getMessage(), __METHOD__);
                        }
                    }
                }
            }
        }

        if (!$model->save()) {
            throw new ModelSaveException($model);
        }
    }

    /**
     * @throws Throwable
     * @throws ModelSaveException
     * @throws InvalidConfigException
     * @throws StaleObjectException
     */
    private function _importRelation(array $relation, BaseActiveRecord $model, string $relatedModel, $relatedData): void
    {
        if (is_array($relatedData)) {
            foreach ($relatedData as &$modelDatum) {
                /* @var BaseActiveRecord|ExportImportBehavior $subModel */
                $subModel = new $relatedModel();
                $modelDatum[$relation[0]] = $model->{$relation[1]};
                $subModel->import($modelDatum, false);
            }
            unset($modelDatum);
        } else {
            /* @var BaseActiveRecord|ExportImportBehavior $subModel */
            $subModel = new $relatedModel();
            $relatedData[$relation[0]] = $model->{$relation[1]};
            $subModel->import($relatedData, false);
        }
    }
}
