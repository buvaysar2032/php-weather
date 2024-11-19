<?php

namespace common\components\arrayQuery\conditions;

/**
 * LessThan checks if value is less than to the data searched.
 *
 * @package common\components\arrayQuery\conditions
 */
class LessThan extends Condition
{
    /**
     * Returns [[GreaterThan]] condition
     */
    public function reverse(): GreaterThan
    {
        return new GreaterThan($this->value);
    }


    /**
     * {@inheritdoc}
     */
    public function matches(mixed $data): bool
    {
        return ($this->checkType($data, $this->value) && $data < $this->value);
    }
}
