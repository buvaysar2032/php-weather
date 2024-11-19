<?php

namespace common\components\arrayQuery\conditions;

/**
 * Condition abstract base class where all conditions extend from
 *
 * @package common\components\arrayQuery\conditions
 */
abstract class Condition
{
    /**
     * @var mixed the value to match against
     */
    protected mixed $value;

    /**
     * @var bool whether to reverse or not
     */
    protected bool $negate = false;

    /**
     * @param mixed $value the value to match against
     */
    public function __construct(mixed $value)
    {
        $this->value = $value;
    }

    /**
     * Reverses the condition
     * @return $this
     */
    public function reverse(): self|static
    {
        $this->negate = !$this->negate;
        return $this;
    }

    /**
     * Checks whether the value passes condition
     *
     * @param mixed $data the data to match
     */
    abstract public function matches(mixed $data): bool;

    /**
     * Checks whether the value and the data are of same type
     *
     * @param mixed $value
     * @param mixed $against
     *
     * @return bool true if both are of same type
     */
    protected function checkType(mixed $value, mixed $against): bool
    {
        if (is_numeric($value) && is_numeric($against)) {
            $value = filter_var($value, FILTER_SANITIZE_NUMBER_FLOAT);
            $against = filter_var($against, FILTER_SANITIZE_NUMBER_FLOAT);
        }

        return gettype($value) === gettype($against);
    }
}
