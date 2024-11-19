<?php

namespace common\enums;

/**
 * Simple boolean enum for tinyint field
 *
 * @package common\enums
 * @author m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
enum Boolean: int implements DictionaryInterface
{
    use DictionaryTrait;

    case No = 0;
    case Yes = 1;

    /**
     * {@inheritdoc}
     */
    public function description(): string
    {
        return match ($this) {
            self::No => 'Нет',
            self::Yes => 'Да'
        };
    }

    /**
     * {@inheritdoc}
     */
    public function color(): string
    {
        return match ($this) {
            self::No => 'var(--bs-danger)',
            self::Yes => 'var(--bs-success)'
        };
    }
}