<?php

namespace common\components\behaviors;

use yii\base\{Behavior, NotSupportedException};
use yii\db\{ActiveRecord, Exception};

/**
 * Class InsertUpdateBehavior
 *
 * @package common\components\behaviors
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 * @property ActiveRecord $owner
 */
class InsertUpdateBehavior extends Behavior
{
    /**
     * Create Query INSERT ... ON DUPLICATE KEY UPDATE ...
     *
     * @throws NotSupportedException
     * @throws Exception
     */
    public function insertUpdate(array $dataInsert, array $columns = null): bool|int
    {
        if (!$dataInsert) {
            return false;
        }

        $db = $this->owner::getDb();
        $onDuplicateKeyValues = [];

        if (!$columns) {
            $columns = $this->owner->attributes();
        }

        foreach ($columns as $itemColumn) {
            $column = $db->getSchema()->quoteColumnName($itemColumn);
            $onDuplicateKeyValues[] = $column . ' = VALUES(' . $column . ')';
        }
        $sql = $db->queryBuilder->batchInsert($this->owner::tableName(), $columns, $dataInsert);
        $sql .= ' ON DUPLICATE KEY UPDATE ' . implode(', ', $onDuplicateKeyValues);
        return $db->createCommand($sql)->execute();
    }

    /**
     * Create Query INSERT IGNORE
     *
     * @throws Exception
     */
    public function insertIgnore(array $dataInsert, $columns = null): bool|int
    {
        if (!$dataInsert) {
            return false;
        }

        $db = $this->owner::getDb();
        if (!$columns) {
            $columns = $this->owner->attributes();
        }

        $sql = $db->queryBuilder->batchInsert($this->owner::tableName(), $columns, $dataInsert);
        $sql = str_replace('INSERT INTO', 'INSERT IGNORE', $sql);
        return $db->createCommand($sql)->execute();
    }
}
