<?php

namespace common\components\queue;

use common\components\helpers\UserFileHelper;
use yii\db\Exception;
use yii\helpers\ArrayHelper;
use yii\mutex\MysqlMutex;
use yii\queue\db\Queue;

/**
 * Class AppQueue
 *
 * @package common\components\jobs
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
class AppQueue extends Queue
{
    public $loopConfig = AppLoop::class;

    public $mutex = MysqlMutex::class;

    public $mutexTimeout = 5;

    /**
     * {@inheritdoc}
     */
    public function behaviors(): array
    {
        return ArrayHelper::merge(
            parent::behaviors(),
            ['refreshDb' => RefreshDbBehavior::class]
        );
    }

    /**
     * {@inheritdoc}
     * @throws Exception
     */
    protected function release($payload): void
    {
        $this->db->close();
        $this->db->open();
        parent::release($payload);
    }

    /**
     * Поставить очередь на паузу
     *
     * @throws \yii\base\Exception
     */
    public function pause(): self
    {
        UserFileHelper::saveDataToFile(['isPaused' => true], 'pause', 'admin', 'loop');
        return $this;
    }

    /**
     * Возобновить выполнение очереди
     *
     * @throws \yii\base\Exception
     */
    public function resume(): self
    {
        UserFileHelper::saveDataToFile(['isPaused' => false], 'pause', 'admin', 'loop');
        return $this;
    }

    /**
     * Стоит ли очередь на паузе
     */
    public function isPaused(): bool
    {
        $data = UserFileHelper::getDataFromFile('pause', 'admin', 'loop');
        return $data && isset($data['isPaused']) && $data['isPaused'];
    }
}
