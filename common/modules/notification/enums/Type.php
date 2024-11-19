<?php

namespace common\modules\notification\enums;

use common\enums\{DictionaryInterface, DictionaryTrait};

/**
 * Class Type
 *
 * @package common\modules\notification\enums
 * @author m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
enum Type: string implements DictionaryInterface
{
    use DictionaryTrait;

    case Success = 'success';
    case Warning = 'warning';
    case Error = 'danger';
    case Info = 'info';

    /**
     * {@inheritdoc}
     */
    public function description(): string
    {
        return match ($this) {
            self::Success => 'Успех',
            self::Warning => 'Предупреждение',
            self::Error => 'Ошибка',
            self::Info => 'Информация'
        };
    }

    /**
     * {@inheritdoc}
     */
    public function color(): string
    {
        return match ($this) {
            self::Success => 'var(--bs-success)',
            self::Warning => 'var(--bs-warning)',
            self::Error => 'var(--bs-danger)',
            self::Info => 'var(--bs-info)'
        };
    }
}