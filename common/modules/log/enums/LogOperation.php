<?php

namespace common\modules\log\enums;

use common\enums\DictionaryInterface;
use common\enums\DictionaryTrait;

/**
 * Class LogOperation
 *
 * @author m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
enum LogOperation: int implements DictionaryInterface
{
    use DictionaryTrait;

    case Delete = 0;
    case Insert = 1;
    case Update = 2;

    /**
     * {@inheritdoc}
     */
    public function description(): string
    {
        return match ($this) {
            self::Delete => 'Удаление',
            self::Insert => 'Добавление',
            self::Update => 'Изменение'
        };
    }

    /**
     * {@inheritdoc}
     */
    public function color(): string
    {
        return match ($this) {
            self::Delete, self::Insert, self::Update => 'var(--bs-body-color)'
        };
    }
}