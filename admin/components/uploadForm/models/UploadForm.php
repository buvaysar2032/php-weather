<?php

namespace admin\components\uploadForm\models;

use yii\base\Model;
use yii\web\UploadedFile;

/**
 * Основа модели для загрузки файлов
 *
 * Можно использовать как основу, наследуясь от неё
 *
 * @package admin\components\uploadForm\models
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
class UploadForm extends Model
{
    /**
     * Аттрибут файла
     */
    public null|string|UploadedFile $file = null;

    /**
     * Label input-а
     */
    public string $label = 'Загрузите файл';

    /**
     * Допустимые расширения файла
     */
    public string $extensions = 'csv,xlsx,ods';

    /**
     * {@inheritdoc}
     */
    final public function rules(): array
    {
        return [
            [
                'file',
                'file',
                'skipOnEmpty' => false,
                'extensions' => $this->extensions,
                'checkExtensionByMimeType' => false
            ]
        ];
    }

    /**
     * {@inheritdoc}
     */
    final public function attributeLabels(): array
    {
        return ['file' => $this->label];
    }
}
