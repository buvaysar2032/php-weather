<?php

namespace common\components\arrayQuery\conditions;

/**
 * NotLike checks if value is not within the data searched.
 *
 *
 * @package common\components\arrayQuery\conditions
 */
class NotLike extends Like
{
    /**
     * {@inheritdoc}
     */
    public function matches(mixed $data): bool
    {
        return !parent::matches($data);
    }
}
