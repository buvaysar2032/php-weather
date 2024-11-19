<?php

namespace api\behaviors\returnStatusBehavior;

use Yii;
use yii\base\{Arrayable, Behavior, Component, Model};
use yii\db\Exception;

/**
 * Class ReturnStatusBehavior
 *
 * @package api\behaviors\returnStatusBehavior
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
class ReturnStatusBehavior extends Behavior
{
    /**
     * Формирование успешного ответа
     */
    final public function returnSuccess(
        Arrayable|array|string $data = null,
        string $header = 'data',
        int $statusCode = 200
    ): array {
        Yii::$app->response->statusCode = $statusCode;
        return $data !== null ? [$header => $data] : [];
    }

    /**
     * Формирование ответа ошибки
     */
    final public function returnError(
        Arrayable|array|string $error = null,
        Arrayable|array|string $error_description = null,
        int $statusCode = 500
    ): ?array {
        return $this->_returnError($error, $error_description, $statusCode);
    }

    /**
     * Формирование ответа с описанием ошибки и её логирование
     */
    private function _returnError(
        Arrayable|array|string|null $error_id,
        Arrayable|array|string|null $error_description,
        int $statusCode = null
    ): ?array {
        if ($statusCode) {
            Yii::$app->response->statusCode = $statusCode;
        }
        if (is_string($error_id)) {
            $response['name'] = $error_id;
            $response['message'] = is_string($error_description)
                ? ['-' => $error_description]
                : $this->removeNestedArrays($error_description);
        } else {
            $response['name'] = 'Unknown error';
            $response['message'] = $this->removeNestedArrays($error_id);
        }
        return $response;
    }

    /**
     * Clear output array of Components from infinite nesting
     *
     * @param mixed $array        Data
     * @param int   $nestingLevel Nesting deep level
     */
    private function removeNestedArrays(mixed &$array, int $nestingLevel = 0): array
    {
        if ($array instanceof Arrayable) {
            $array = $array->toArray();
        }
        if (!is_array($array)) {
            return [];
        }
        if (1 < $nestingLevel++) {
            return [];
        }
        foreach ($array as &$value) {
            if (is_array($value)) {
                $this->removeNestedArrays($value, $nestingLevel);
            } elseif ($value instanceof Model) {
                $value = ['class' => $value::class] + $value->toArray();
            } elseif ($value instanceof Component) {
                $value = $value::class;
            }
        }
        return $array;
    }

    /**
     * Формирование ответа ошибки 400
     */
    final public function returnErrorBadRequest(): array
    {
        return $this->_returnError('request:bad', 'Некорректный запрос', 400);
    }

    /**
     * Формирование ответа ошибки 401 (пользователь не авторизован)
     */
    final public function returnErrorUserIsNotLoggedIn(): array
    {
        return $this->_returnError('user:not_logged_in', 'Пользователь не авторизован', 401);
    }

    /**
     * Формирование ответа ошибки 401 (пользователь не найден)
     */
    final public function returnUserNotFoundError(): array
    {
        return $this->_returnError('user:not_found', 'Пользователь не найден', 401);
    }

    /**
     * Формирование ответа ошибки 405
     */
    final public function returnActionError(): array
    {
        return $this->_returnError('action:error', 'Страница не найдена', 405);
    }

    /**
     * Формирование ответа ошибки обращения к БД (500)
     */
    final public function getDBError(Exception $error): array
    {
        return $this->_returnError('db:error', [$error->getName() => $error->getMessage()], 500);
    }
}
