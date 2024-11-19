<?php

namespace api\components\requestLimiter;

use Exception;
use JetBrains\PhpStorm\Pure;
use yii\base\UserException;

/**
 * Class RequestLimiterException
 *
 * @package api\components\requestLimiter
 */
class RequestLimiterException extends UserException
{
    /**
     * Constructor.
     *
     * @param string|null    $message  error message
     * @param int            $code     error code
     * @param Exception|null $previous The previous exception used for the exception chaining.
     */
    #[Pure]
    public function __construct(string $message = null, int $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
