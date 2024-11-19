<?php

namespace common\components\helpers;

use common\models\AppActiveRecord;
use Yii;
use yii\helpers\Url;

class UserUrl extends Url
{
    /**
     * Путь к папке uploads относительно webroot
     */
    public const UPLOADS = '/uploads/global';

    /**
     * Список GET параметров для запоминания
     */
    public static array $rememberQueryParams = ['page', 'per-page', 'sort'];

    /**
     * @param AppActiveRecord|string $searchModelName Имя класса фильтруемой модели
     * @param array                  $url             Конструкция подходящая для [[Url::toRoute()]] функции
     *
     * @return array Новая конструкция
     */
    final public static function setFilters(string|AppActiveRecord $searchModelName, array $url = ['index']): array
    {
        $basename = basename(str_replace('\\', DIRECTORY_SEPARATOR, $searchModelName));
        $sessionName = "_userFilter_$basename";
        $data = Yii::$app->session->get($sessionName);
        if (!is_array($data)) {
            $data = [];
        }
        if (
            array_key_exists(0, $url) &&
            !empty($url[0])
        ) {
            if (array_key_exists($basename, $data) && !empty($data[$basename])) {
                $url[$basename] = $data[$basename];
            }
            foreach (self::$rememberQueryParams as $rememberQueryParam) {
                if (array_key_exists($rememberQueryParam, $data) && !empty($data[$rememberQueryParam])) {
                    $url[$rememberQueryParam] = $data[$rememberQueryParam];
                }
            }
        }
        return $url;
    }

    /**
     * Генерация ссылки со сброшенными фильтрами
     *
     * @param AppActiveRecord|string $searchModelName Имя класса фильтруемой модели
     * @param array                  $url             Конструкция подходящая для [[Url::toRoute()]] функции
     *
     * @return array Новая конструкция
     */
    final public static function clearFilters(string|AppActiveRecord $searchModelName, array $url = ['index']): array
    {
        $basename = basename(str_replace('\\', DIRECTORY_SEPARATOR, $searchModelName));
        $sessionName = "_userFilter_$basename";
        $data = Yii::$app->session->get($sessionName);
        if (!is_array($data)) {
            $data = [];
        }
        if (array_key_exists('page', $data) && !empty($data['page'])) {
            $url['page'] = $data['page'];
        }
        if (array_key_exists('per-page', $data) && !empty($data['per-page'])) {
            $url['per-page'] = $data['per-page'];
        }
        return $url;
    }

    final public static function toAbsolute(array|string|null $url): ?string
    {
        if (empty($url)) {
            return null;
        }
        if (str_starts_with($url, self::UPLOADS) && $time = self::getFileUpdateTime($url)) {
             $url = [$url, '_t' => $time];
        }
        if (is_array($url)) {
            $baseUrl = (string)array_shift($url);
            if (!empty($url)) {
                $baseUrl .= '?' . http_build_query($url);
            }
            $url = $baseUrl;
        }
        return self::to($url, Yii::$app->request->isSecureConnection ? 'https' : 'http');
    }

    private static function getFileUpdateTime(string $fileUrl): ?int
    {
        $filename = Yii::getAlias('@root/htdocs/') . ltrim(urldecode($fileUrl), '/');
        if (file_exists($filename) && !is_dir($filename)) {
            return filectime($filename);
        }
        return null;
    }
}
