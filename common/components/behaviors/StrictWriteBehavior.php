<?php

namespace common\components\behaviors;

use common\models\AppActiveRecord;
use Throwable;
use Yii;
use yii\base\Behavior;
use yii\db\{BaseActiveRecord, Connection, Exception};

/**
 * @property AppActiveRecord $owner
 */
class StrictWriteBehavior extends Behavior
{
    /**
     * {@inheritdoc}
     */
    final public function events(): array
    {
        return [
            BaseActiveRecord::EVENT_BEFORE_UPDATE => 'saveState',
            BaseActiveRecord::EVENT_BEFORE_DELETE => 'saveState',
            BaseActiveRecord::EVENT_AFTER_INSERT => 'afterInsert',
            BaseActiveRecord::EVENT_AFTER_UPDATE => 'afterUpdate',
            BaseActiveRecord::EVENT_AFTER_DELETE => 'afterDelete',
        ];
    }

    private array $oldState;

    final public function saveState(): void
    {
        $this->oldState = $this->owner->oldAttributes;
    }

    /**
     * @throws Exception
     * @throws Throwable
     */
    final public function afterInsert(): void
    {
        $this->checkWrite(true);
    }

    /**
     * @throws Throwable
     * @throws Exception
     */
    final public function afterUpdate(): void
    {
        $this->checkWrite(false);
    }

    /**
     * @throws Throwable
     * @throws Exception
     */
    final public function afterDelete(): void
    {
        $this->checkWrite(false);
    }

    /**
     * @throws Exception
     * @throws Throwable
     */
    final public function checkWrite(bool $insert): void
    {
        $db = $this->owner::getDb();
        if (self::getPosWait($db) !== 0) {
            if ($insert) {
                $this->owner->delete();
            } elseif (isset($this->oldState)) {
                $this->owner->setAttributes($this->oldState);
                $this->owner->save(false);
            }
            throw new Exception('Replica timeout exception');
        }
    }

    /**
     * @throws Exception
     */
    public static function getPosWait(Connection $db): int
    {
        if (
            Yii::$app->environment->DB_SLAVE_HOSTS
            && $db->transaction === null // При активной транзакции не будет работать
            && str_starts_with($db->dsn, 'mysql')
            && $db->getSlavePdo() !== $db->getMasterPdo() // Проверяем что есть живой slave
            && $status = $db->createCommand('SHOW MASTER STATUS')->queryOne()
        ) {
            $file = $status['File'];
            $position = $status['Position'];

            // Проверка версии MySQL
            $version = $db->createCommand('SELECT VERSION()')->cache(3600)->queryOne()['VERSION()'];
            // Выбор актуальной SQL команды
            if (version_compare($version, '8.0.26') === -1) {
                $sql = 'MASTER_POS_WAIT';
            } else {
                $sql = 'SOURCE_POS_WAIT';
            }

            $result = $db->createCommand("SELECT $sql(:file, :position, 30) AS result")
                ->bindValues(['file' => $file, 'position' => $position])
                ->queryOne()['result'];
            return (int)$result;
        }
        return 0;
    }
}
