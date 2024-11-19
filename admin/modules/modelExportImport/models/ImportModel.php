<?php

namespace admin\modules\modelExportImport\models;

use yii\base\Model;
use yii\web\UploadedFile;

/**
 * Class ImportModel
 *
 * @package modelExportImport\models
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
class ImportModel extends Model
{
    /**
     * Импортируемый файл
     */
    public ?UploadedFile $file = null;

    /**
     * {@inheritdoc}
     */
    final public function rules(): array
    {
        return [
            ['file', 'file']
        ];
    }

    /**
     * {@inheritdoc}
     */
    final public function attributeLabels(): array
    {
        return [
            'file' => 'Файл'
        ];
    }
}