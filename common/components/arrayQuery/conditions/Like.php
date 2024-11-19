<?php

namespace common\components\arrayQuery\conditions;

/**
 * Like checks if value is matches any words in theå data searched.
 *
 * @package common\components\arrayQuery\conditions
 */
class Like extends Condition
{

    /**
     * {@inheritdoc}
     */
    public function matches(mixed $data): bool
    {
        return is_string($data) && mb_stripos($data, $this->value) !== false;
    }
}
