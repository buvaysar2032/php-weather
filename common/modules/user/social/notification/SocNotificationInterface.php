<?php

namespace common\modules\user\social\notification;

/**
 * Interface SocNotificationInterface
 *
 * @package user\social\notification
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
interface SocNotificationInterface
{
    /**
     * Отправка уведомления пользователю в соц. сети
     */
    public function send(): bool;

    /**
     * Получение ссылки на метод отправки уведомления.
     */
    public function getMethodUrl(): string;

    /**
     * Получение массива параметров для уведомления
     */
    public function getMethodParams(): array;
}