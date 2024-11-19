<?php

namespace common\components\queue;

use yii\base\Behavior;
use yii\db\Exception;
use yii\queue\{cli\Queue as CliQueue, Queue};

/**
 * Class RefreshDdBehavior
 *
 * @package common\components\jobs
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 *
 * @property \yii\queue\db\Queue $owner
 */
class RefreshDbBehavior extends Behavior
{
    /**
     * {@inheritdoc}
     */
    public function events(): array
    {
        return [
            Queue::EVENT_AFTER_EXEC => 'refreshDb',
            Queue::EVENT_AFTER_ERROR => 'refreshDb',
            CliQueue::EVENT_WORKER_STOP => 'refreshDb',
        ];
    }

    /**
     * @throws Exception
     */
    public function refreshDb(): void
    {
        $this->owner->db->close();
        $this->owner->db->open();
    }
}
