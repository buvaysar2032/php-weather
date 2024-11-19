<?php

namespace common\models;

use common\components\helpers\ModuleHelper;
use Yii;
use yii\base\InvalidConfigException;
use yii\db\{ActiveQuery, Query};
use yii\helpers\ArrayHelper;

/**
 * Class AppActiveQuery
 *
 * @package models
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 *
 * @see     AppActiveRecord
 */
class AppActiveQuery extends ActiveQuery
{
    /** @var AppActiveRecord|string */
    public $modelClass;

    /**
     * {@inheritdoc}
     */
    final public function count($q = '*', $db = null): bool|int|string|null
    {
        $this->distinctCountQuery($q);
        return parent::count($q, $db);
    }

    private function distinctCountQuery(string &$q): void
    {
        if (
            $q === '*'
            && !$this->distinct
            && empty($this->groupBy)
            && empty($this->having)
            && empty($this->union)
        ) {
            $keys = $this->modelClass::primaryKey();
            foreach ($keys as &$key) {
                $key = $this->modelClass::tableName() . '.' . Yii::$app->db->schema->quoteSimpleColumnName($key);
            }
            unset($key);
            foreach ($keys as $key) {
                $q = implode(', ', $keys);
            }
            if (
                ModuleHelper::isAdminModule()
                && !empty($this->where)
                && empty($this->join)
                && empty($this->link)
                && !empty($this->modelClass::externalAttributes())
            ) {
                $q = "DISTINCT $q";
                $joinWith = $_ = [];
                foreach ($this->modelClass::externalAttributes() as $attribute) {
                    $this->modelClass::parseQueryField($attribute, $_, $joinWith);
                }
                $this->joinWith($joinWith);
            }
        }
    }

    /**
     * {@inheritdoc}
     * @throws InvalidConfigException
     */
    public function prepare($builder): Query
    {
        if (
            ModuleHelper::isAdminModule()
            && empty($this->select)
            && empty($this->join)
            && empty($this->link)
            && !empty($this->modelClass::externalAttributes())
        ) {
            $select = [$this->modelClass::tableName() . '.*'];
            $joinWith = [];
            foreach ($this->modelClass::externalAttributes() as $attribute) {
                $this->modelClass::parseQueryField($attribute, $select, $joinWith);
            }
            $this->select($select);
            $this->joinWith($joinWith);
        }
        $query = parent::prepare($builder);
        if (!empty($query->join)) {
            [, $alias] = $this->getTableNameAndAlias();
            $schema = $this->modelClass::getTableSchema();
            $columnNames = ArrayHelper::getColumn($schema->columns, 'name');
            if (!empty($query->where)) {
                $this->prefixTableAlias($query->where, $alias, $columnNames);
            }
            if (!empty($query->having)) {
                $this->prefixTableAlias($query->having, $alias, $columnNames);
            }
        }
        return $query;
    }

    /**
     * @param string[] $columns
     */
    private function prefixTableAlias(array &$where, string $alias, array $columns): void
    {
        foreach ($where as $key => &$item) {
            if (is_string($key) && in_array($key, $columns) && !str_contains($key, '.')) {
                $where["$alias.$key"] = $item;
                unset($where[$key]);
            }
            if (is_array($item)) {
                if (
                    array_is_list($item)
                    && count($item) === 3
                    && in_array(
                        strtolower($item[0]),
                        ['and', 'or', 'like', 'between', '>', '<', '>=', '<=', '!=', '=', 'not in', 'not']
                    )
                    && in_array($item[1], $columns, true)
                    && !str_contains($item[1], '.')
                ) {
                    $item[1] = "$alias.$item[1]";
                }
                $this->prefixTableAlias($item, $alias, $columns);
            }
        }
    }
}
