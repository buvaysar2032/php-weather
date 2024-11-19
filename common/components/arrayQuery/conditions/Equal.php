<?php

namespace common\components\arrayQuery\conditions;

/**
 * Equal checks if value is equal to the data searched.
 *
 * @package common\components\arrayQuery\conditions
 */
class Equal extends Condition
{

    /**
     * {@inheritdoc}
     */
    public function matches(mixed $data): bool
    {
        return (($this->checkType($data, $this->value) && strcmp($data, $this->value) === 0) xor $this->negate);
    }
}
