<?php

namespace api\modules\v1\controllers;

use Exception;
use Yii;
use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;
use yii\httpclient\Client;

class WeatherController extends AppController
{
    private string $apiUrl = 'https://api.weather.yandex.ru/v2/forecast';
    private string $apiKey;
    private float $latitude;
    private float $longitude;

    /**
     * @throws InvalidConfigException
     */
    public function init(): void
    {
        parent::init();
        $this->apiKey = Yii::$app->environment->YANDEX_WEATHER_API_KEY;
        $this->latitude = (float)Yii::$app->environment->DEFAULT_LATITUDE;
        $this->longitude = (float)Yii::$app->environment->DEFAULT_LONGITUDE;
    }

    public function behaviors(): array
    {
        return ArrayHelper::merge(parent::behaviors(), ['auth' => ['except' => ['index']]]);
    }

    /**
     * @throws Exception
     */
    public function actionIndex()
    {
        $cacheKey = "weather_{$this->latitude}_{$this->longitude}";

        $cachedData = Yii::$app->cache->get($cacheKey);
        if ($cachedData !== false) {
            return json_decode($cachedData, true);
        }

        $client = new Client();
        $response = $client->createRequest()
            ->setMethod('GET')
            ->setUrl("{$this->apiUrl}?lat={$this->latitude}&lon={$this->longitude}")
            ->addHeaders(['X-Yandex-Weather-Key' => $this->apiKey])
            ->send();

        if (!$response->isOk) {
            Yii::error("Ошибка API: статус {$response->statusCode}", __METHOD__);
            throw new Exception('Сервис временно недоступен. Пожалуйста, попробуйте позже.');
        }

        Yii::$app->cache->set($cacheKey, $response->content, 1800); // Кешируем успешный ответ на 30 минут

        return json_decode($response->content, true);
    }
}
