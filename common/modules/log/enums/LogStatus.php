<?php

namespace common\modules\log\enums;

use common\enums\{DictionaryInterface, DictionaryTrait};

/**
 * Class LogStatus
 *
 * @package common\modules\log\enums
 * @author m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
enum LogStatus: int implements DictionaryInterface
{
    use DictionaryTrait;

    case Error = 0;
    case Success = 10;

    /**
     * {@inheritdoc}
     */
    public function description(): string
    {
        return match ($this) {
            self::Error => 'Ошибка',
            self::Success => 'Успешно'
        };
    }

    /**
     * {@inheritdoc}
     */
    public function color(): string
    {
        return match ($this) {
            self::Error => 'var(--bs-danger)',
            self::Success => 'var(--bs-success)'
        };
    }
}