<?php

namespace common\components\helpers;

use Exception;
use Yii;
use yii\helpers\{FileHelper, Json};

/**
 * Class FileHelper
 *
 * @package common\components
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 * @author  d.potehin <d.potehin@peppers-studio.ru>
 */
class UserFileHelper extends FileHelper
{
    /**
     * Тип сохраняемого файла по умолчанию.
     */
    private const DEFAULT_FILETYPE = '.json';

    /**
     * Конвертация строки размера информации в число байт.
     */
    public static function stringToBytes(string $value): string
    {
        return preg_replace_callback(
            '/^\s*(\d+)\s*([kmgt]?)b?\s*$/i',
            static function ($m) {
                switch (strtolower($m[2])) {
                    case 't':
                        $m[1] *= 1024 ** 4;
                        break;
                    case 'g':
                        $m[1] *= 1024 ** 3;
                        break;
                    case 'm':
                        $m[1] *= 1024 ** 2;
                        break;
                    case 'k':
                        $m[1] *= 1024;
                        break;
                    default:
                        break;
                }
                return $m[1];
            },
            $value
        );
    }

    /**
     * Конвертация числа байт в строку.
     */
    public static function bytesToString(int|string $value = null, string $to = null): int|string|null
    {
        if ($value === null) {
            return $value;
        }
        $l = ['B', 'K', 'M', 'G', 'T'];
        $value = (int)$value;

        if (!$to) {
            foreach ($l as $iValue) {
                if (floor($value / 1024) <= 0) {
                    $value = round($value, 2) . $iValue;
                    break;
                }
                $value /= 1024;
            }
            return $value;
        }

        $to = strtoupper($to);
        $index = array_search($to, $l, true);
        if ($index !== false) {
            $value = (round($value / (1024 ** $index))) . $l[$index];
        }
        return $value;
    }

    /**
     * Удаление картинки или файла
     *
     * @param string $filename Имя файла
     * @param string $app      Имя приложения
     * @param string $category Категория json файла
     */
    public static function deleteFile(string $filename, string $app = 'common', string $category = 'saved'): void
    {
        $jsonFile = self::_getRuntimePath($app) . "/$category/$filename" . self::DEFAULT_FILETYPE;
        if (file_exists($jsonFile)) {
            unlink($jsonFile);
        }
    }

    /**
     * Сохранение данных в json файл.
     *
     * @param array|string $data     Данные.
     * @param string       $filename Имя файла.
     * @param string       $app      Имя приложения.
     * @param string       $category Категория json файла.
     *
     * @throws \yii\base\Exception
     */
    public static function saveDataToFile(
        array|string $data,
        string $filename,
        string $app = 'common',
        string $category = 'saved'
    ): string {
        if (!is_string($data)) {
            $data = Json::encode($data);
        }
        $path = self::_getRuntimePath($app) . "/$category";
        self::createDirectory($path);
        $jsonFile = "$path/$filename" . self::DEFAULT_FILETYPE;
        file_put_contents($jsonFile, $data);
        @chmod($jsonFile, 0776);
        return $jsonFile;
    }

    /**
     * Получение данных из json файла.
     *
     * @param string $filename Имя файла.
     * @param string $app      Имя приложения.
     * @param string $category Категория json файла.
     */
    public static function getDataFromFile(
        string $filename,
        string $app = 'admin',
        string $category = 'saved'
    ): bool|array|string {
        $jsonFile = self::_getRuntimePath($app) . "/$category/$filename" . self::DEFAULT_FILETYPE;
        if (is_file($jsonFile)) {
            $content = file_get_contents($jsonFile);
            try {
                return Json::decode($content);
            } catch (Exception) {
                return $content;
            }
        }
        return false;
    }

    /**
     * Проверка существования файла.
     *
     * @param string $filename Имя файла.
     * @param string $app      Имя приложения.
     * @param string $category Категория json файла.
     */
    public static function fileExists(string $filename, string $app = 'common', string $category = 'saved'): bool
    {
        return file_exists(self::_getRuntimePath($app) . "/$category/$filename" . self::DEFAULT_FILETYPE);
    }

    /**
     * Получить путь до каталога runtime
     *
     * @param string $app Имя приложения
     *
     * @return string Путь до каталога runtime в приложении
     */
    private static function _getRuntimePath(string $app = 'common'): string
    {
        return Yii::getAlias("@root/$app/runtime");
    }
}
