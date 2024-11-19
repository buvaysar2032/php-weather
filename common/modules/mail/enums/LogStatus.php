<?php

namespace common\modules\mail\enums;

use common\enums\{DictionaryInterface, DictionaryTrait};

/**
 * Interface LogStatus
 *
 * @package common\modules\mail\enums
 * @author m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
enum LogStatus: int implements DictionaryInterface
{
    use DictionaryTrait;

    case Error = 0;
    case Success = 10;
    case Repeated = 20;

    /**
     * {@inheritdoc}
     */
    public function description(): string
    {
        return match ($this) {
            self::Error => 'Ошибка',
            self::Success => 'Успешно',
            self::Repeated => 'Повторено'
        };
    }

    /**
     * {@inheritdoc}
     */
    public function color(): string
    {
        return match ($this) {
            self::Error => 'var(--bs-danger)',
            self::Success => 'var(--bs-success)',
            self::Repeated => 'var(--bs-warning)'
        };
    }
}