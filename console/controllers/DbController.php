<?php

namespace console\controllers;

use common\components\helpers\UserFileHelper;
use SplFileInfo;
use Yii;
use yii\console\{Exception, ExitCode};
use yii\helpers\BaseConsole;
use ZipArchive;

/**
 * Резервное копирование БД
 *
 * @package console\controllers
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 *
 * @property null|string $dump
 */
final class DbController extends ConsoleController
{
    public string $dumpPath = '@root/dumps';

    /**
     * Импорт
     *
     * @throws Exception
     */
    public function actionImport(string $path = null): int
    {
        if ($path) {
            $this->dumpPath = $path;
        }
        return $this->importDump();
    }

    /**
     * Распаковка архива
     */
    private function unZip(): void
    {
        $path = $this->dumpPath;
        $path = UserFileHelper::normalizePath(Yii::getAlias($path));
        $destinationPath = UserFileHelper::normalizePath(Yii::getAlias('@root/htdocs/uploads/global'));
        $dir = opendir($path);
        while (false !== ($file = readdir($dir))) {
            $ext = new SplFileInfo($file);
            $ext = $ext->getExtension();
            if ($ext === 'zip') {
                $zip = new ZipArchive();
                $zip->open("$path/$file");
                $this->stdout('Найден архив: ' . $file . PHP_EOL);
                for ($i = 0; $i < $zip->numFiles; $i++) {
                    $item = $zip->getNameIndex($i);
                    $zipExt = new SplFileInfo($item);
                    $zipExt = $zipExt->getExtension();
                    if ($zipExt !== 'sql') {
                        $zip->extractTo($destinationPath, $item);
                    } else {
                        $zip->extractTo($path, $item);
                    }
                    $this->stdout('Распакован файл: ' . $item . PHP_EOL);
                }
                $zip->close();
            }
        }
    }

    /**
     * Импорт дампа БД
     *
     * @throws Exception
     */
    public function importDump(): int
    {
        $path = $this->dumpPath;
        $path = UserFileHelper::normalizePath(Yii::getAlias($path));

        if (!is_dir($path)) {
            throw new Exception('Path must exist and must be a directory', ExitCode::OSFILE);
        }
        $this->unZip();
        $files = UserFileHelper::findFiles($path, ['only' => ['*.sql']]);

        if (!$files) {
            throw new Exception('Path does not contain any SQL files', ExitCode::OSFILE);
        }

        if (count($files) > 1) {
            $select = $this->select('Select SQL file', $files);
            if ($this->confirm('Confirm selected file [' . $files[$select] . ']')) {
                $path = $files[$select];
            } else {
                return ExitCode::OK;
            }
        } else {
            $path = $files[0];
        }

        $db = Yii::$app->getDb();
        if (!$db) {
            throw new Exception('DB component not configured', ExitCode::CONFIG);
        }

        exec(
            'mysql --host=' . $this->getDsnAttribute('host', $db->dsn) .
            ' --user=' . $db->username .
            ' --password=' . $db->password .
            ' ' . $this->getDsnAttribute('dbname', $db->dsn) . ' < ' . $path
        );
        $this->stdout('Dump file [' . $path . '] was imported' . PHP_EOL);
        $this->deleteDump($path);
        return ExitCode::OK;
    }

    /**
     * Достать параметр из DSN строки подключения
     *
     * @param string $name Название атрибута
     * @param string $dsn  DSN строка подключения
     */
    private function getDsnAttribute(string $name, string $dsn): ?string
    {
        if (preg_match('/' . $name . '=([^;]*)/', $dsn, $match)) {
            return $match[1];
        }
        return null;
    }

    /**
     * Очистка дампов БД
     */
    public function deleteDump(string $path): void
    {
        if (file_exists($path)) {
            unlink($path);
        }
    }

    /**
     * Экспорт
     *
     * @throws Exception
     * @throws \yii\base\Exception
     */
    public function actionExport(string $path = null): int
    {
        if ($path) {
            $this->dumpPath = $path;
        }
        return $this->createArchive();
    }

    /**
     * @throws Exception
     * @throws \yii\base\Exception
     */
    private function createArchive(): int
    {
        $pathDir = '@root/htdocs/uploads/global/'; // путь к папке, файлы которой будем архивировать
        $pathDir = UserFileHelper::normalizePath(Yii::getAlias($pathDir));
        $savePath = UserFileHelper::normalizePath(Yii::getAlias($this->dumpPath));
        $nameArchive = date('Y-m-d H-m-s') . '_content.zip'; //название архива
        $zip = new ZipArchive(); // класс для работы с архивами
        // создаем архив, если все прошло удачно продолжаем
        if ($zip->open("$savePath/$nameArchive", ZipArchive::CREATE) !== true) {
            throw new Exception('Произошла ошибка при создании архива', ExitCode::UNSPECIFIED_ERROR);
        }

        $dir = opendir($pathDir); // открываем папку с файлами
        // перебираем все файлы из нашей папки
        while (false !== ($file = readdir($dir))) {
            // проверяем файл ли мы взяли из папки
            if ($file !== '.' && $file !== '..' && !is_dir($pathDir . '\\' . $file)) {
                $zip->addFile("$pathDir/$file", $file); // и архивируем
            }
        }
        $dumpName = $this->getDump();
        $dumpDir = opendir($savePath);
        if (readdir($dumpDir)) {
            $zip->addFile("$savePath/$dumpName", $dumpName);
        }
        if (!$zip->close()) { // закрываем архив.
            throw new Exception('Произошла ошибка при создании архива', ExitCode::UNSPECIFIED_ERROR);
        }
        $this->stdout('Архив успешно создан', BaseConsole::FG_GREEN);
        unlink("$savePath/$dumpName");
        return ExitCode::OK;
    }

    /**
     * Создание дампа БД
     *
     * @throws Exception
     * @throws \yii\base\Exception
     */
    private function getDump(): string
    {
        $path = $this->dumpPath;
        $path = UserFileHelper::normalizePath(Yii::getAlias($path));
        UserFileHelper::createDirectory($path);
        if (!is_writable($path)) {
            throw new Exception('Path must be a directory and writable', ExitCode::OSFILE);
        }
        $fileName = 'dump-' . date('Y-m-d-H-i-s') . '.sql';
        $fileName = $this->prompt('Enter filename: ', ['default' => $fileName]);
        $filePath = "$path/$fileName";
        $db = Yii::$app->getDb();
        if (!$db) {
            throw new Exception('DB component not configured', ExitCode::CONFIG);
        }
        exec(
            'mysqldump --host=' . $this->getDsnAttribute('host', $db->dsn) .
            ' --user=' . $db->username .
            ' --password=' . $db->password .
            ' ' . $this->getDsnAttribute('dbname', $db->dsn) .
            ' --skip-add-locks  > ' . $filePath
        );
        return $fileName;
    }
}