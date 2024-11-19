<?php

namespace common\components\events;

use Throwable;
use yii\base\Event;

/**
 * Class ErrorEvent
 *
 * @package common\components
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
class ErrorEvent extends Event
{
    public Throwable $exception;
}
