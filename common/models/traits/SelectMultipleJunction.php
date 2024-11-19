<?php

namespace common\models\traits;

use common\components\exceptions\ModelSaveException;
use Throwable;
use yii\db\StaleObjectException;

/**
 * Interface SelectMultipleJunction
 *
 * @package common\models\traits
 * @author m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
interface SelectMultipleJunction
{
    /**
     * Добавить связь выбора в БД
     *
     * @throws ModelSaveException
     */
    public static function add(int $modelId, int|string $subModelId): bool;

    /**
     * Удалить связь выбора из БД
     *
     * @throws Throwable
     * @throws StaleObjectException
     */
    public static function remove(int $modelId, int|string $subModelId): bool;
}
