<?php

use common\components\{Environment, queue\AppQueue};
use yii\queue\{LogBehavior, redis\Queue};

if (
    !empty(Environment::readEnv('REDIS_HOSTNAME'))
    && !empty(Environment::readEnv('REDIS_PORT'))
) {
    $queue = [
        'class' => Queue::class,
        'as log' => LogBehavior::class
    ];
} else {
    $queue = AppQueue::class;
}
return $queue;
