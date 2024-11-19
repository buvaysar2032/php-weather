<?php

namespace common\modules\user\enums;

use common\enums\{DictionaryInterface, DictionaryTrait};

/**
 * Class Status
 *
 * @package common\modules\user\enums
 * @author m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
enum Status: int implements DictionaryInterface
{
    use DictionaryTrait;

    case New = 0;
    case Active = 10;
    case Blocked = 20;

    /**
     * {@inheritdoc}
     */
    public function description(): string
    {
        return match ($this) {
            self::New => 'Новый',
            self::Active => 'Активен',
            self::Blocked => 'Заблокирован'
        };
    }

    /**
     * {@inheritdoc}
     */
    public function color(): string
    {
        return match ($this) {
            self::New => 'var(--bs-body-color)',
            self::Active => 'var(--bs-success)',
            self::Blocked => 'var(--bs-danger)'
        };
    }
}