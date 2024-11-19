<?php

namespace common\components\queue;

use yii\db\Exception;
use yii\queue\cli\SignalLoop;

/**
 * Class AppLoop
 *
 * @package common\components\jobs
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 * @property AppQueue $queue
 */
class AppLoop extends SignalLoop
{
    /**
     * @throws Exception
     */
    public function canContinue(): bool
    {
        $isSlept = $this->queue->isPaused();
        while ($this->queue->isPaused()) {
            usleep(2000);
        }
        if ($isSlept) {
            $this->queue->db->close();
            $this->queue->db->open();
        }
        return parent::canContinue();
    }
}
