<?php

namespace common\components\arrayQuery\conditions;

/**
 * GreaterThanOrEqual checks if value is greater or equal to the data searched.
 *
 * @package common\components\arrayQuery\conditions
 */
class GreaterThanOrEqual extends Condition
{

    /**
     * Returns [[LessThanOrEqual]] condition
     */
    public function reverse(): LessThanOrEqual
    {
        return new LessThanOrEqual($this->value);
    }

    /**
     * {@inheritdoc}
     */
    public function matches(mixed $data): bool
    {
        $gt = new GreaterThan($this->value);
        $e = new Equal($this->value);

        return $gt->matches($data) || $e->matches($data);
    }
}
