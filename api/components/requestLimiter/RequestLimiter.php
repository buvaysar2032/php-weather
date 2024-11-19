<?php

namespace api\components\requestLimiter;

use common\components\exceptions\ModelSaveException;
use common\modules\user\helpers\UserHelper;
use Exception;
use Yii;
use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;
use yii\web\HttpException;

/**
 * Class RequestLimiter
 *
 * @package api\components\requestLimiter
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
class RequestLimiter
{
    /**
     * Префикс для хранения данных сессии, чтобы избежать возможных коллизий
     */
    public const SESSION_PREFIX = '_RL__';

    /**
     * @throws InvalidConfigException
     * @throws HttpException
     */
    public static function checkCategory(string $category, bool $create_session = false): array
    {
        $session_data = self::getSessionData($category, $create_session);
        if ($session_data) {
            self::checkCooldown($session_data, $create_session);
        }
        self::saveSession($session_data);
        return $session_data;
    }

    /**
     * @throws InvalidConfigException
     * @throws Exception
     */
    public static function getSessionData(bool|string $category = null, bool $create_session = true): array
    {
        if ($category === true) { // Авто-генерация категории
            $category = Yii::$app->controller->id . '_' . Yii::$app->controller->action->id;
        }

        $limit_settings = ArrayHelper::getValue(Yii::$app->params, 'request_limits.' . $category);
        if (!$limit_settings) {
            throw new InvalidConfigException('RequestLimiter: config data required');
        }

        $session = Yii::$app->session;
        $user_session = $session->get(self::SESSION_PREFIX . $category);
        $current_time = time();

        $result = [
            'category' => $category,
            'current_time' => $current_time,
        ];

        // Если это первая, то запись возвращаем что все ок
        if (!$user_session && $create_session) {
            $user_session = [
                'block_state' => 0,
                'errors' => [],
                'values' => []
            ];
            $result['is_first_time'] = true;
        }
        $result['user_session'] = $user_session;
        $result['prev_time'] = $result['user_session']['last_time'] ?? null;
        $result['user_session']['last_time'] = $current_time;
        $result['limit_settings'] = $limit_settings;

        if ($user_session) {
            $count = count($user_session['values'] ?? []);
            if ($count) {
                $result['last_value'] = $user_session['values'][$count - 1];
            }
        }

        return $result;
    }

    /**
     * @throws HttpException
     */
    public static function checkCooldown(array &$session_data, bool $save_if_first_time = false): ?bool
    {
        if (!$session_data) {
            return null;
        }

        // Если первая запись, то выходим из проверки частоты запроса
        if ($session_data['is_first_time'] ?? null) {
            if ($save_if_first_time) {
                self::saveSession($session_data);
            }
            return false;
        }

        // Проверка записи на ограничение доступа
        $cooldown = $session_data['user_session']['cooldown'] ?? null;
        if ($cooldown) {
            $dif = $cooldown - $session_data['current_time'];
            if ($dif > 0) {
                self::throwCooldownException($dif);
            }
            unset($session_data['user_session']['cooldown']);
            self::saveSession($session_data);
        }

        // Если настроено ограничение частоты запросов
        $request_cooldown = $session_data['limit_settings']['request_cooldown'];
        if ($request_cooldown) {
            $last_entry_time_diff = $session_data['current_time'] - $session_data['prev_time']; // Время с последнего записанного вызова
            // ПРОВЕРКА ОГРАНИЧЕНИЯ МЕЖДУ ЗАПРОСАМИ
            if ($last_entry_time_diff < $request_cooldown) {
                self::throwCooldownException($request_cooldown - $last_entry_time_diff, 'Concurrent requests exceeded');
            }
        }
        return false;
    }

    /**
     * Сохранить сессию
     */
    public static function saveSession(array $session_data): void
    {
        if (!$session_data) {
            return;
        }
        Yii::$app->session->set(self::SESSION_PREFIX . $session_data['category'], $session_data['user_session']);
    }

    /**
     * @throws HttpException
     */
    public static function throwCooldownException(int $dif, string $message = null): void
    {
        Yii::$app->response->headers->add('X-Request-Limiter-Cooldown', $dif);
        throw new HttpException(403, 'RequestLimiter: ' . ($message ? $message . ' ' : '') . 'Cooldown: ' . $dif);
    }

    /**
     * Добавить введенной значение в сессию
     *
     * @param array  $session_data Данные
     * @param string $value_name   Имя переменной
     * @param mixed  $value        Значение
     * @param bool   $save         Сохранить сессию
     */
    public static function addSessionValue(array &$session_data, string $value_name, mixed $value, bool $save = false): void
    {
        $current_value = [
            $value_name => $value,
            'time' => time(),
            'ip' => Yii::$app->request->userIP
        ];

        $session_data['user_session']['values'][] = $current_value;
        $session_data['current_value'] = $current_value;
        if ($save) {
            self::saveSession($session_data);
        }
    }

    /**
     * Увеличить счетчик ошибок
     *
     * @throws InvalidConfigException
     * @throws ModelSaveException
     */
    public static function addSessionWrongValue(array &$session_data, bool $save = true): ?bool
    {
        $errors_settings = $session_data['limit_settings']['blocking_levels'];
        if (!$errors_settings) {
            throw new InvalidConfigException('RequestLimiter: "errors" setting is missing');
        }

        $block_state = &$session_data['user_session']['block_state'];
        $errors_settings = $errors_settings[$block_state];

        $current_time = $session_data['current_time'];
        $user_errors = &$session_data['user_session']['errors'];
        $user_errors[] = $current_time;
        $border_time = $current_time - ($errors_settings['error_period'] ?? $errors_settings['period']);

        while (count($user_errors) && $user_errors[0] < $border_time) {
            array_shift($user_errors);
        }
        // Проверяем кол-во ошибок за период
        if (count($user_errors) >= $errors_settings['max_errors']) {
            self::setNextBlockLevel($session_data, false);
            self::clearErrors($session_data);
            return false;
        }
        if ($save) {
            self::saveSession($session_data);
        }
        return true;
    }

    /**
     * Установить следующий уровень блокировки
     *
     * @throws InvalidConfigException
     * @throws ModelSaveException
     * @throws Exception
     */
    public static function setNextBlockLevel(array &$session_data, bool $save = true): bool
    {
        $user_session = &$session_data['user_session'];
        $user_session['block_state'] = (int)($user_session['block_state'] ?? 0);
        $cooldown_delay = ArrayHelper::getValue(
            $session_data,
            'limit_settings.blocking_levels.' . $user_session['block_state'] . '.cooldown'
        );
        if (!$cooldown_delay) {
            throw new InvalidConfigException(
                'RequestLimiter: Not found value for "limit_settings.blocking_levels.' .
                $user_session['block_state'] . '.cooldown"'
            );
        }

        // Поднимаем уровень блокировки
        if ($user_session['block_state'] < count($session_data['limit_settings']['blocking_levels']) - 1) {
            $user_session['block_state']++;
        } elseif ($session_data['limit_settings']['permanent_block_after_max_level']) {
            UserHelper::blockCurrentUser();
        }

        self::setCooldown(
            $session_data,
            $cooldown_delay,
            'Gain blocking level up to ' . $user_session['block_state'],
            false
        );
        if ($save) {
            self::saveSession($session_data);
        }

        return true;
    }

    /**
     * Ограничить доступ на время
     *
     * @param array  $session_data   Данные сессии
     * @param int    $cooldown_time  Время
     * @param string $cooldown_cause Причина
     * @param bool   $save           Сохранить сессию
     */
    public static function setCooldown(
        array &$session_data,
        int $cooldown_time,
        string $cooldown_cause,
        bool $save = true
    ): void {
        $session_data['user_session']['cooldown'] = $session_data['current_time'] + $cooldown_time;
        $session_data['user_session']['cooldown_cause'] = $cooldown_cause;

        Yii::$app->response->headers->add('X-Request-Limiter-Cooldown', $cooldown_time);

        if ($save) {
            self::saveSession($session_data);
        }
    }

    /**
     * Очистить список ошибок в сессии
     */
    public static function clearErrors(array &$session_data, bool $save = true): bool
    {
        $session_data['user_session']['errors'] = [];
        if ($save) {
            self::saveSession($session_data);
        }
        return true;
    }

    /**
     * Добавить введенное значение
     *
     * @throws InvalidConfigException
     * @throws ModelSaveException
     */
    public static function addBruteForceValue(array &$session_data, mixed $value, bool $save = true): ?bool
    {
        $errors_settings = $session_data['limit_settings']['blocking_levels'] ?? null;
        if (!$errors_settings) {
            throw new InvalidConfigException('RequestLimiter: "errors" setting is missing');
        }

        $block_state = &$session_data['user_session']['block_state'];
        $errors_settings = $errors_settings[$block_state] ?? null;

        $current_time = $session_data['current_time'];
        $user_values = &$session_data['user_session']['values'];
        $user_values[] = [$current_time, $value];
        $border_time = $current_time - ($errors_settings['bf_period'] ?? $errors_settings['period'] ?? 0);

        while (count($user_values) && $user_values[0][0] < $border_time) {
            array_shift($user_values);
        }
        // Проверяем кол-во разных значений за период
        $values = array_unique(ArrayHelper::getColumn($user_values, 1));
        $various_count = count($values);
        if ($various_count >= ($errors_settings['max_bf_values'] ?? 0)) {
            self::setNextBlockLevel($session_data, false);
            self::clearErrors($session_data);
            return false;
        }
        if ($save) {
            self::saveSession($session_data);
        }
        return null;
    }

    /**
     * Очистить список введенных значений
     */
    public static function clearBruteForce(array &$session_data, bool $save = true): void
    {
        $session_data['user_session']['values'] = [];
        if ($save) {
            self::saveSession($session_data);
        }
    }

    /**
     * Получить данные категории
     */
    public static function getCategoryData(string $category, string $param_name): mixed
    {
        $session = Yii::$app->session;
        $user_session = $session->get(self::SESSION_PREFIX . $category);
        return $user_session[$param_name];
    }

    /**
     * Очистить категорию
     */
    public static function clearCategoryData(array|string $session_or_category_name): void
    {
        $session = Yii::$app->session;
        if (!is_string($session_or_category_name)) {
            $session->remove(self::SESSION_PREFIX . $session_or_category_name['category']);
        }
        $session->remove(self::SESSION_PREFIX . $session_or_category_name);
    }
}