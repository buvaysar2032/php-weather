<?php

namespace common\modules\user\social;

use yii\httpclient\Response;

/**
 * Базовый интерфейс SocInterface для взаимодействия с соц. сетями для авторизации с помощью OAuth
 *
 * @package user\social
 * @author m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 *
 */
interface SocInterface
{
    /**
     * Получение url для OAuth авторизации в соц. сети
     */
    public function getLoginUrl(): string;

    /**
     * Получение токена доступа к соц. сети
     */
    public function getAccessTokenUrl(): string;

    /**
     * Получение url к API соц. сети для получения данных пользователя
     */
    public function getUserUrl(): string;

    /**
     * Получение параметров авторизации OAuth
     */
    public function getAuthArgs(): array;

    /**
     * Получение параметров для доступа к информации пользователя
     */
    public function getUserArgs(Response $response): array;

    /**
     * Получение id пользователя из ответа соц. сети
     */
    public function getUserIdFromResponse(Response $response): mixed;

    /**
     * Получение данных пользователя из ответа соц. сети
     */
    public function getUserDataFromResponse(Response $response): array;

    /**
     * Получение токена доступа из ответа соц. сети
     */
    public function getAccessTokenFromResponse(Response $response): string;
}