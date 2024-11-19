<?php

namespace api\modules\v1\controllers;

use Exception;
use Yii;
use yii\helpers\ArrayHelper;

class WeatherController extends AppController
{
    public function behaviors(): array
    {
        return ArrayHelper::merge(parent::behaviors(), ['auth' => ['except' => ['index']]]);
    }

    private string $apiUrl = 'https://api.weather.yandex.ru/v2/forecast';
    private string $apiKey = 'c21102a8-9f57-42ee-bbaf-34f89ad69742';

    /**
     * @throws Exception
     */
    public function actionIndex($lat, $lon)
    {
        $cacheKey = "weather_{$lat}_{$lon}";

        $cachedData = Yii::$app->cache->get($cacheKey);
        if ($cachedData !== false) {
            return json_decode($cachedData, true);
        }

        $opts = [
            'http' => [
                'method' => 'GET',
                'header' => "X-Yandex-Weather-Key: {$this->apiKey}\r\n",
            ],
        ];

        $context = stream_context_create($opts);
        $url = "{$this->apiUrl}?lat={$lat}&lon={$lon}";

        $response = @file_get_contents($url, false, $context);

        if ($response === false) {
            throw new Exception('Ошибка при получении данных с Яндекс Погоды: ' . error_get_last()['message']);
        }

        // Кешируем ответ на 30 минут
        Yii::$app->cache->set($cacheKey, $response, 1800);

        return json_decode($response, true);
    }
}
