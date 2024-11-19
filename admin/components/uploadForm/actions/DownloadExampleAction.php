<?php

namespace admin\components\uploadForm\actions;

use Yii;
use yii\base\Action;
use yii\web\Response;

/**
 * Действие для скачивания примера файла для загрузки
 *
 * @package admin\components\uploadForm\actions
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
class DownloadExampleAction extends Action
{
    /**
     * @var string[] Путы к файлам примерам относительно папки examples
     */
    public array $paths = [
        'areas.csv'
    ];

    final public function run(string $type = 'csv'): Response
    {
        $dir = dirname(__DIR__) . '/examples/';
        $typePaths = $this->getPathsByType($type);
        $path = $typePaths[array_rand($typePaths)];
        return Yii::$app->response->sendFile($dir . $path);
    }

    /**
     * Получить список файлов совпадающих по типу
     */
    private function getPathsByType(string $type): array
    {
        return array_filter($this->paths, static fn ($item) => preg_match("/\.$type$/", $item));
    }
}
