<?php

namespace common\components\arrayQuery\conditions;

/**
 * LessThanOrEqual checks if value is less than or equal to the data searched.
 *
 *
 * @package common\components\arrayQuery\conditions
 */
class LessThanOrEqual extends Condition
{

    /**
     * Returns [[GreaterThanOrEqual]] condition
     */
    public function reverse(): GreaterThanOrEqual
    {
        return new GreaterThanOrEqual($this->value);
    }

    /**
     * {@inheritdoc}
     */
    public function matches(mixed $data): bool
    {
        $gt = new LessThan($this->value);
        $e = new Equal($this->value);

        return $gt->matches($data) || $e->matches($data);
    }
}
