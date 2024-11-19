<?php

namespace admin\components\uploadForm\models;

use admin\components\parsers\ParserInterface;

/**
 * Interface UploadInterface
 *
 * @package admin\components\uploadForm\models
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
interface UploadInterface
{
    /**
     * Вставить данные в БД из csv файла загруженного в модель UploadForm
     *
     * @param UploadForm      $model  Модель с загруженным файлом верного формата
     * @param ParserInterface $parser Парсер загруженного файла
     */
    public static function insertFromFile(UploadForm $model, ParserInterface $parser): void;
}
