<?php

namespace common\components;

use ZipArchive;

/**
 * Расширенный ZipArchive для рекурсивной упаковки каталогов в архив
 *
 * @package common\components
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
class ExtendedZip extends ZipArchive
{
    /**
     * Helper function
     */
    public static function zipTree(
        string $dirname,
        string $zipFilename,
        array $exceptions = [],
        int $flags = 0,
        string $localName = ''
    ): void {
        $zip = new self();
        $zip->open($zipFilename, $flags);
        $zip->addTree($dirname, $exceptions, $localName);
        $zip->close();
    }

    /**
     * {@inheritdoc}
     */
    final public function open($filename, $flags = null): void
    {
        if (file_exists($filename)) {
            chmod($filename, 0777);
        }
        parent::open($filename, $flags);
    }

    /**
     * Member function to add a whole file system subtree to the archive.
     */
    final public function addTree(string $dirname, array $exceptions, string $localName = ''): void
    {
        if ($localName) {
            $this->addEmptyDir($localName);
        }
        $this->_addTree($dirname, $exceptions, $localName);
    }

    /**
     * Internal function, to recurse.
     */
    final protected function _addTree(string $dirname, array $exceptions, string $localName): void
    {
        $dir = opendir($dirname);

        foreach ($exceptions as $value) { //Проверка исключений
            if (str_contains($dirname, $value)) {
                closedir($dir);
                return;
            }
        }

        while ($filename = readdir($dir)) {
            // Discard . and ..
            if ($filename === '.' || $filename === '..') {
                continue;
            }
            // Proceed according to type
            $path = $dirname . '/' . $filename;
            $localPath = $localName ? ($localName . '/' . $filename) : $filename;
            if (is_dir($path)) {
                // Directory: add & recurse
                $this->addEmptyDir($localPath);
                $this->_addTree($path, $exceptions, $localPath);
            } elseif (is_file($path)) {
                // File: just add
                $this->addFile($path, $localPath);
            }
        }
        closedir($dir);
    }

    /**
     * Вывод текста ошибки.
     *
     * @param int $code Код ошибки.
     */
    public static function getErrorName(int $code): string
    {
        return match ($code) {
            self::ER_EXISTS => 'Файл уже существует.',
            self::ER_INCONS => 'Несовместимый ZIP-архив.',
            self::ER_INVAL => 'Недопустимый аргумент.',
            self::ER_MEMORY => 'Ошибка динамического выделения памяти.',
            self::ER_NOENT => 'Нет такого файла.',
            self::ER_NOZIP => 'Не является ZIP-архивом.',
            self::ER_OPEN => 'Невозможно открыть файл.',
            self::ER_READ => 'Ошибка чтения.',
            self::ER_SEEK => 'Ошибка поиска.',
            default => 'Неизвестная ошибка: ' . print_r($code, true),
        };
    }
}
