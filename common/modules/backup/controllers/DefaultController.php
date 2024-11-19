<?php

namespace common\modules\backup\controllers;

use admin\controllers\AdminController;
use common\components\helpers\UserFileHelper;
use common\modules\backup\{Backup, models\DbWrap};
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Yii;
use yii\db\Exception;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\{Response, UploadedFile};
use ZipArchive;

/**
 * Class DefaultController
 *
 * @package backup\controllers
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
class DefaultController extends AdminController
{
    /**
     * {@inheritdoc}
     */
    public function behaviors(): array
    {
        return ArrayHelper::merge(parent::behaviors(), [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'remove' => ['POST'],
                    'export' => ['POST'],
                    'import' => ['POST'],
                    'tables' => ['POST']
                ]
            ]
        ]);
    }

    /**
     * Открытие страницы для работы с бэкапами
     *
     * @throws \yii\base\Exception
     */
    final public function actionIndex(): string
    {
        set_time_limit(0);
        $data = DbWrap::getBackups();
        return $this->render('index', compact('data'));
    }

    /**
     * Получить список активных бекапов
     *
     * @throws \yii\base\Exception
     */
    final public function actionActiveBackups(): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        return [
            'messages' => ['success' => ['Список бекапов обновлен']],
            'data' => DbWrap::getBackups()
        ];
    }

    /**
     * Экспорт таблицы
     *
     * @throws \yii\base\Exception
     */
    final public function actionExport(): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        // экспорт
        ini_set('memory_limit', '200M');
        set_time_limit(0);
        $json = [];
        if (!$table = Yii::$app->request->post('table')) {
            return ['messages' => ['error' => ['`table` POST field is empty']]];
        }
        $date = Yii::$app->request->post('date', date('Y-m-d_H-i-s'));
        try {
            if (DbWrap::export($table, $date)) {
                $time = microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'];
                $json['messages']['success'][] = Yii::t(
                    Backup::MODULE_MESSAGES,
                    'Export of table {table} completed successfully in {time} seconds',
                    ['table' => $table, 'time' => round($time, 3)]
                );
            } else {
                $json['messages']['warning'][] = Yii::t(
                    Backup::MODULE_MESSAGES,
                    'An error occurred while exporting the table {table}',
                    ['table' => $table]
                );
            }
        } catch (Exception $e) {
            $json['messages']['danger'][] = Yii::t(
                Backup::MODULE_MESSAGES,
                'An error occurred while exporting the table {table}',
                ['table' => $table]
            );
            $json['messages']['danger'][] = $e->getMessage();
            return $json;
        }
        return $json;
    }

    /**
     * Импорт таблицы
     *
     * @throws Exception
     */
    final public function actionImport(): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        ini_set('memory_limit', '200M');
        set_time_limit(0);
        $json = [];
        if (!$table = Yii::$app->request->post('table')) {
            return ['messages' => ['error' => ['`table` POST field is empty']]];
        }
        if (!$dateString = Yii::$app->request->post('date')) {
            return ['messages' => ['error' => ['`date` POST field is empty']]];
        }
        if (DbWrap::import($table, $dateString)) {
            $time = microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'];
            $json['messages']['success'][] = Yii::t(
                Backup::MODULE_MESSAGES,
                'Import of table {table} completed successfully in {time} seconds',
                ['table' => $table, 'time' => round($time, 3)]
            );
        } else {
            $json['messages']['warning'][] = Yii::t(
                Backup::MODULE_MESSAGES,
                'An error occurred while importing the table {table}',
                ['table' => $table]
            );
        }
        return $json;
    }

    /**
     * Удаление всех бэкапов
     */
    final public function actionRemove(): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        ini_set('memory_limit', '200M');
        set_time_limit(0);
        // remove
        $json = [];
        if (DbWrap::removeAll()) {
            $time = microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'];
            $json['messages']['success'][] = Yii::t(
                Backup::MODULE_MESSAGES,
                'Deleting database backup files successfully completed in {time} seconds',
                ['time' => $time]
            );
        } else {
            $json['messages']['warning'][] = Yii::t(
                Backup::MODULE_MESSAGES,
                'An error occurred while deleting database backup files'
            );
        }
        return $json;
    }

    /**
     * Получение списка таблиц
     */
    final public function actionTables(): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        ini_set('memory_limit', '200M');
        set_time_limit(0);
        $json = [];
        try {
            if ($tables = DbWrap::getTables()) {
                $time = microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'];
                $json['messages']['success'][] = Yii::t(
                    Backup::MODULE_MESSAGES,
                    'List of tables successfully retrieved in {time} seconds',
                    ['time' => round($time, 3)]
                );
                $json['data'] = $tables;
            } else {
                $json['messages']['danger'][] = Yii::t(
                    Backup::MODULE_MESSAGES,
                    'An error occurred while retrieving the list of tables'
                );
            }
        } catch (Exception) {
            $json['messages']['danger'][] = Yii::t(
                Backup::MODULE_MESSAGES,
                'An error occurred while retrieving the list of tables'
            );
            return $json;
        }
        return $json;
    }

    /**
     * Упаковка дампа в архив и его скачивание
     *
     * @throws \yii\base\Exception
     */
    final public function actionDownload(): Response
    {
        ini_set('memory_limit', '200M');
        set_time_limit(0);
        $dateString = (!empty($_GET['date'])) ? $_GET['date'] : null;
        DbWrap::downloadFromObjectStorage($dateString);
        $zip = new ZipArchive();
        $path = Yii::getAlias('@admin/runtime/backup_db');
        UserFileHelper::createDirectory($path);
        $zipPath = "$path/$dateString/$dateString.zip";
        $zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator("$path/$dateString"),
            RecursiveIteratorIterator::LEAVES_ONLY
        );
        foreach ($files as $file) {
            if (!$file->isDir()) {
                $filePath = $file->getRealPath();
                // Исключаем упаковку архива в архив
                if (!str_ends_with('.zip', $filePath)) {
                    $relativePath = basename($filePath);
                    $zip->addFile($filePath, $relativePath);
                }
            }
        }
        $zip->close();
        return Yii::$app->response->sendFile($zipPath);
    }

    /**
     *  Загрузка архива с дампом на сервер
     *
     * @throws \yii\base\Exception
     */
    final public function actionUpload(): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        ini_set('memory_limit', '200M');
        set_time_limit(0);
        $json = [];
        if (!$file = UploadedFile::getInstanceByName('file')) {
            $json['messages']['error'][] = 'File not found (request body is empty)';
            return $json;
        }

        $path = Yii::getAlias("@admin/runtime/backup_db/$file->baseName/");
        $filename = "$file->baseName.$file->extension";
        UserFileHelper::createDirectory($path);
        $file->saveAs($path . $filename);
        $zip = new ZipArchive();
        $zip->open($path . $filename);
        $zip->extractTo($path);
        $zip->close();
        DbWrap::uploadToObjectStorage($file->baseName);
        $json['messages']['success'][] = Yii::t(Backup::MODULE_MESSAGES, 'Database backup loaded successfully');
        return $json;
    }
}
