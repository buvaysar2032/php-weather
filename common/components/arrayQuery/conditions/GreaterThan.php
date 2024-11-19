<?php

namespace common\components\arrayQuery\conditions;

/**
 * GreaterThan checks if value is greater than the data searched.
 *
 * @package common\components\arrayQuery\conditions
 */
class GreaterThan extends Condition
{

    /**
     * Returns [[LessThan]] condition
     */
    public function reverse(): LessThan
    {
        return new LessThan($this->value);
    }


    /**
     * {@inheritdoc}
     */
    public function matches(mixed $data): bool
    {
        return ($this->checkType($data, $this->value) && $data > $this->value);
    }

}
