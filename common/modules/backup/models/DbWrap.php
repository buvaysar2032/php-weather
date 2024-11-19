<?php

namespace common\modules\backup\models;

use common\components\helpers\ModuleHelper;
use common\models\traits\ObjectStorageTrait;
use Yii;
use yii\base\Exception;
use yii\db\{ActiveRecord, Exception as DbException};
use yii\helpers\ArrayHelper;
use yii\helpers\BaseConsole;

/**
 * Модель "обертка" для доступа к БД
 *
 * @package backup\models
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
class DbWrap extends ActiveRecord
{
    use ObjectStorageTrait;

    private const RUNTIME_DIR = '@admin/runtime/';

    /**
     * Количество строчек таблиц на один запрос
     */
    protected static int $countSelectRows = 1000;

    /**
     * Название папки для хранения бекапов
     */
    protected static string $dirBackUp = 'backup_db';

    /**
     * SET @OLD_CHARACTER_SET_CLIENT - указываем кодировку на клиенте
     * SET NAMES - указываем нашу кодировку
     * SET @OLD_FOREIGN_KEY_CHECKS - отключаем проверку целостности таблицы БД на время выполнения запроса
     * SET @OLD_SQL_MODE - указываем режим работы mysql сервера
     */
    protected static string $offlineCheckForeignKey = <<<SQL
/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
SQL
    . PHP_EOL;

    /**
     * Резервное копирование всей БД
     *
     * @throws Exception
     * @throws DbException
     */
    public static function exportDB(string $dateString = null): string
    {
        if (!$dateString) {
            $dateString = date('Y-m-d_H-i-s');
        }
        if (ModuleHelper::isConsoleModule()) {
            Yii::$app->controller->stdout("Getting tables list..." . PHP_EOL);
        }
        $tables = self::getTables();
        foreach ($tables as $table) {
            if (ModuleHelper::isConsoleModule()) {
                Yii::$app->controller->stdout("Exporting `$table` table..." . PHP_EOL);
            }
            try {
                self::export($table, $dateString);
                if (ModuleHelper::isConsoleModule()) {
                    Yii::$app->controller->stdout('Done' . PHP_EOL, BaseConsole::FG_GREEN);
                }
            } catch (\Exception $exception) {
                if (ModuleHelper::isConsoleModule()) {
                    Yii::$app->controller->stdout($exception->getMessage() . PHP_EOL, BaseConsole::BG_RED);
                }
            }
        }
        return $dateString;
    }

    /**
     * Резервное копирование таблицы
     *
     * @throws Exception
     * @throws DbException
     */
    public static function export(string $table, string $dateString): bool
    {
        if (!$table || !$dateString) {
            return false;
        }
        $typesNoString = [
            'float',
            'double',
            'decimal',
            'bit',
            'int',
            'smallint',
            'mediumint',
            'bigint',
            'tinyint'
        ];

        // выбираем подключение
        $dbRemote = self::getDb();
        $ENV = Yii::$app->environment;
        $dbName = self::getDsnAttribute('dbname', $dbRemote->dsn);
        $file = Yii::getAlias(self::RUNTIME_DIR . self::$dirBackUp) . "/$dbName-$dateString/$table.sql";
        if (
            !file_exists($concurrentDirectory = dirname($file)) &&
            !mkdir($concurrentDirectory, 0755, true) &&
            !is_dir($concurrentDirectory)
        ) {
            throw new Exception(sprintf('Directory "%s" was not created', $concurrentDirectory));
        }
        if (self::hasS3Storage()) {
            try {
                if (!self::getS3Client()->doesObjectExist($ENV->S3_PRIVATE_BUCKET, "$dbName-$dateString")) {
                    self::getS3Client()->putObject([
                        'Bucket' => $ENV->S3_PRIVATE_BUCKET,
                        'Key' => "$dbName-$dateString/"
                    ]);
                }
            } catch (\Exception $exception) {
                Yii::error($exception->getMessage(), __METHOD__);
                Yii::$app->session->addFlash('error', $exception->getMessage());
            }
        }

        // Получим дамп на создание
        $sqlShowCreateTable = <<<SQL
SHOW CREATE TABLE `$table`;
SQL;
        $create = $dbRemote->createCommand($sqlShowCreateTable)->noCache()->queryOne()['Create Table'];

        $insertPrefix = "INSERT INTO `$table` (";
        // собираем типы данных
        $fieldTypes = [];
        $sqlColumns = <<<SQL
SHOW COLUMNS FROM `$table`;
SQL;
        $dataColumns = $dbRemote->createCommand($sqlColumns)->noCache()->queryAll();
        foreach ($dataColumns as $dataColumn) {
            if ($strPos = strpos($dataColumn['Type'], '(')) {
                $fieldTypes[$dataColumn['Field']] = trim(substr($dataColumn['Type'], 0, $strPos));
            } else {
                $fieldTypes[$dataColumn['Field']] = trim($dataColumn['Type']);
            }
            $insertPrefix .= "`{$dataColumn['Field']}`,";
        }
        $insertPrefix = trim(trim($insertPrefix, ',')) . ') VALUES ';
        $create = self::$offlineCheckForeignKey . <<<SQL
DROP TABLE IF EXISTS `$table`;
SQL
            . PHP_EOL . "$create;";

        self::write($file, $create . PHP_EOL);

        $limit = (self::$countSelectRows) ?: 100;
        // Делаем INSERT
        $sqlCount = <<<SQL
SELECT count(*) as `count` FROM `$table`
SQL;
        $count = (int)$dbRemote->createCommand($sqlCount)->noCache()->queryOne()['count'];
        $pages = ceil($count / $limit);

        for ($page = 0; $page <= $pages; $page++) {
            $offset = $page * $limit;

            $sqlSelect = <<<SQL
SELECT * FROM `$table` LIMIT $offset, $limit;
SQL;

            $list = $dbRemote->createCommand($sqlSelect)->noCache()->queryAll();

            if (!empty($list)) {
                $insert = self::_dataToQueryString($list, $fieldTypes, $typesNoString);

                $insert = $insertPrefix . trim($insert, ',') . ';';
                self::write($file, $insert . PHP_EOL);
            }
        }
        if (self::hasS3Storage()) {
            try {
                self::getS3Client()->upload($ENV->S3_PRIVATE_BUCKET, "$dbName-$dateString/$table.sql",
                    fopen($file, 'r'));
            } catch (\Exception $exception) {
                Yii::error($exception->getMessage(), __METHOD__);
                Yii::$app->session->addFlash('error', $exception->getMessage());
            }
        }
        return true;
    }

    private static function _dataToQueryString(array $list, array $fieldTypes, array $typesNoString): string
    {
        $insert = '';
        foreach ($list as $item) {
            $insert .= '(';
            $str = '';
            foreach ($item as $k => $v) {
                // определяем необходимость в кавычках
                if (in_array($fieldTypes[$k], $typesNoString, true)) {
                    if (is_null($v)) {
                        $str .= 'NULL,';
                    } elseif (empty($v)) {
                        $str .= "'$v',";
                    } else {
                        $str .= "$v,";
                    }
                } else {
                    $str .= "'" . addslashes((string)$v) . "',";
                }
            }
            $insert .= trim($str, ',') . '),';
        }
        return $insert;
    }

    public static function write(string $file, string $content): bool|int
    {
        $f = fopen($file, 'ab');
        $res = fwrite($f, $content);
        fclose($f);
        return $res;
    }

    /**
     * Возвращает название хоста (например localhost)
     */
    private static function getDsnAttribute(string $name, string $dsn): string
    {
        if (preg_match("/$name=([^;]*)/", $dsn, $match)) {
            return $match[1];
        }
        return '';
    }

    /**
     * Импортирует последний бекап
     *
     * @throws DbException
     * @throws Exception
     */
    public static function import(string $table, string $dateString = null): bool
    {
        if (!$table) {
            return false;
        }

        $dir = Yii::getAlias(self::RUNTIME_DIR) . self::$dirBackUp . "/$dateString/";
        if (!file_exists($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
            throw new Exception(sprintf('Directory "%s" was not created', $dir));
        }
        $file = "$dir/$table.sql";
        if (
            self::hasS3Storage()
            && self::getS3Client()->doesObjectExist(Yii::$app->environment->S3_PRIVATE_BUCKET, "$dateString/$table.sql")
        ) {
            self::getS3Client()->getObject([
                'Bucket' => Yii::$app->environment->S3_PRIVATE_BUCKET,
                'Key' => "$dateString/$table.sql",
                'SaveAs' => $file,
            ]);
        }
        if (!file_exists($file)) {
            return false;
        }

        $db = self::getDb();
        $db->createCommand(file_get_contents($file))->noCache()->execute();
        return true;
    }

    /**
     * @throws Exception
     */
    public static function downloadFromObjectStorage(string $dateString): void
    {
        if (!self::hasS3Storage()) {
            return;
        }
        $dir = Yii::getAlias(self::RUNTIME_DIR) . self::$dirBackUp . "/$dateString/";
        if (!file_exists($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
            throw new Exception(sprintf('Directory "%s" was not created', $dir));
        }
        $ENV = Yii::$app->environment;
        try {
            $result = self::getS3Client()->listObjectsV2([
                'Bucket' => $ENV->S3_PRIVATE_BUCKET,
                'Prefix' => "$dateString/"
            ]);
            foreach ($result['Contents'] as $object) {
                $filename = $object['Key'];
                $saveAs = $dir . basename($filename);
                self::getS3Client()->getObject([
                    'Bucket' => $ENV->S3_PRIVATE_BUCKET,
                    'Key' => $filename,
                    'SaveAs' => $saveAs,
                ]);
            }
        } catch (\Exception $exception) {
            Yii::error($exception->getMessage(), __METHOD__);
            Yii::$app->session->addFlash('error', $exception->getMessage());
        }
    }

    public static function uploadToObjectStorage(string $dateString): void
    {
        if (!self::hasS3Storage()) {
            return;
        }
        try {
            $dir = Yii::getAlias(self::RUNTIME_DIR) . self::$dirBackUp . "/$dateString/";
            self::getS3Client()->uploadDirectory($dir, Yii::$app->environment->S3_PRIVATE_BUCKET, "$dateString/");
        } catch (\Exception $exception) {
            Yii::error($exception->getMessage(), __METHOD__);
            Yii::$app->session->addFlash('error', $exception->getMessage());
        }
    }

    /**
     * Проверяет тип ОС
     */
    public static function isWindows(): bool
    {
        $php_uname = php_uname();
        $arr = explode('Windows', $php_uname);
        return count($arr) > 1;
    }

    /**
     * Удаляет все ранее созданные бекапы
     */
    public static function removeAll(): bool
    {
        if (self::isWindows()) {
            $command = 'RD /S/q "' .
                str_replace(
                    '/',
                    '\\',
                    Yii::getAlias(self::RUNTIME_DIR) . self::$dirBackUp
                ) . '\"';
        } else {
            $command = 'cd ' .
                Yii::getAlias(self::RUNTIME_DIR) .
                self::$dirBackUp . ' && rm -rf ';
        }
        shell_exec($command);
        return true;
    }

    /**
     * Удаляет бэкап
     */
    public static function remove(string $dateString): bool
    {
        $dbRemote = self::getDb();
        $dbName = self::getDsnAttribute('dbname', $dbRemote->dsn);
        if (self::isWindows()) {
            $command = 'RD /S/q "' .
                str_replace('/', '\\', Yii::getAlias(self::RUNTIME_DIR) . self::$dirBackUp) .
                "\\$dbName-$dateString\\\"";
        } else {
            $command = 'cd ' .
                Yii::getAlias(self::RUNTIME_DIR) . self::$dirBackUp .
                "/$dbName-$dateString && rm -rf ";
        }
        shell_exec($command);
        if (self::hasS3Storage()) {
            self::getS3Client()->deleteMatchingObjects(Yii::$app->environment->S3_PRIVATE_BUCKET, "$dbName-$dateString/");
        }
        return true;
    }

    /**
     * Получение списка таблиц
     *
     * @throws DbException
     */
    public static function getTables(): bool|array
    {
        $sqlShowTables = <<<'SQL'
SHOW TABLES
SQL;
        $db = self::getDb();
        $tablesTemp = $db->createCommand($sqlShowTables)->queryAll();
        if (empty($tablesTemp)) {
            return false;
        }
        $temps = [];
        foreach ($tablesTemp as $temp) {
            $temps[] = array_values($temp);
        }
        return array_merge(...$temps);
    }

    /**
     * Получение списка бэкапов.
     *
     * @throws Exception
     */
    public static function getBackups(): array
    {
        $path = Yii::getAlias(self::RUNTIME_DIR) . self::$dirBackUp;
        if (!file_exists($path) && !mkdir($path) && !is_dir($path)) {
            throw new Exception(sprintf('Directory "%s" was not created', $path));
        }
        $backups = array_values(array_diff(scandir($path), ['.', '..']));
        if (self::hasS3Storage()) {
            try {
                $result = self::getS3Client()
                    ->listObjectsV2([
                        'Bucket' => Yii::$app->environment->S3_PRIVATE_BUCKET,
                        'Delimiter' => '/'
                    ])
                    ->get('CommonPrefixes');
                if (is_array($result)) {
                    $result = ArrayHelper::getColumn($result, 'Prefix');
                    foreach ($result as &$item) {
                        $item = trim($item, '/');
                    }
                    $backups = array_unique(array_merge($backups, $result));
                }
            } catch (\Exception $exception) {
                Yii::error($exception->getMessage(), __METHOD__);
                Yii::$app->session->addFlash('error', $exception->getMessage());
            }
        }
        return $backups;
    }
}
