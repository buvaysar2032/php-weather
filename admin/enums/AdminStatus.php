<?php

namespace admin\enums;

use common\enums\{DictionaryInterface, DictionaryTrait};

/**
 * Class AdminStatus
 *
 * @package admin\enums
 * @author m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
enum AdminStatus: int implements DictionaryInterface
{
    use DictionaryTrait;

    case Inactive = 0;
    case Active = 10;

    /**
     * {@inheritdoc}
     */
    public function description(): string
    {
        return match ($this) {
            self::Inactive => 'Неактивен',
            self::Active => 'Активен'
        };
    }

    /**
     * {@inheritdoc}
     */
    public function color(): string
    {
        return match ($this) {
            self::Inactive => 'var(--bs-body-color)',
            self::Active => 'var(--bs-success)'
        };
    }
}