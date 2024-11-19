<?php

namespace common\modules\gii\generators\model;

use ReflectionClass;
use yii\base\NotSupportedException;
use yii\db\{Connection, Schema, TableSchema};
use yii\gii\generators\model\Generator as ModelGenerator;

/**
 * Class Generator
 *
 * @package gii\model
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
class Generator extends ModelGenerator
{
    /**
     * {@inheritdoc}
     */
    final public function generateRules($table): array
    {
        $types = [];
        $lengths = [];
        foreach ($table->columns as $column) {
            if ($column->autoIncrement) {
                continue;
            }
            if (
                !$column->allowNull
                && $column->defaultValue === null
                && $column->name !== 'created_at'
                && $column->name !== 'updated_at'
            ) {
                $types['required'][] = $column->name;
            }
            switch ($column->type) {
                case Schema::TYPE_SMALLINT:
                case Schema::TYPE_INTEGER:
                case Schema::TYPE_BIGINT:
                case Schema::TYPE_TINYINT:
                    $types['integer'][] = $column->name;
                    break;
                case Schema::TYPE_BOOLEAN:
                    $types['boolean'][] = $column->name;
                    break;
                case Schema::TYPE_FLOAT:
                case Schema::TYPE_DOUBLE:
                case Schema::TYPE_DECIMAL:
                case Schema::TYPE_MONEY:
                    $types['number'][] = $column->name;
                    break;
                case Schema::TYPE_DATE:
                case Schema::TYPE_TIME:
                case Schema::TYPE_DATETIME:
                case Schema::TYPE_TIMESTAMP:
                case Schema::TYPE_JSON:
                    $types['safe'][] = $column->name;
                    break;
                default: // strings
                    if ($column->size > 0) {
                        $lengths[$column->size][] = $column->name;
                    } else {
                        $types['string'][] = $column->name;
                    }
            }
        }
        $rules = [];

        $this->_generateSimpleRules($rules, $types, $lengths);

        if ($db = $this->getDbConnection()) {
            $this->_generateUniqueIndexRules($rules, $table, $db);

            $this->_generateExistRules($rules, $table, $db);
        }

        return $rules;
    }

    private function _generateSimpleRules(array &$rules, array $types, array $lengths): void
    {
        $driverName = $this->getDbDriverName();
        foreach ($types as $type => $columns) {
            if ($driverName === 'pgsql' && $type === 'integer') {
                $rules[] = "[['" . implode("', '", $columns) . "'], 'default', 'value' => null]";
            }
            $rules[] = "[['" . implode("', '", $columns) . "'], '$type']";
        }
        foreach ($lengths as $length => $columns) {
            $rules[] = "[['" . implode("', '", $columns) . "'], 'string', 'max' => $length]";
        }
    }

    /**
     * Unique indexes rules
     */
    private function _generateUniqueIndexRules(array &$rules, TableSchema $table, Connection $db): void
    {
        try {
            $uniqueIndexes = array_merge($db->getSchema()->findUniqueIndexes($table), [$table->primaryKey]);
            $uniqueIndexes = array_unique($uniqueIndexes, SORT_REGULAR);
            foreach ($uniqueIndexes as $uniqueColumns) {
                // Avoid validating auto incremental columns
                if (!$this->isColumnAutoIncremental($table, $uniqueColumns)) {
                    $attributesCount = count($uniqueColumns);

                    if ($attributesCount === 1) {
                        $rules[] = "[['" . $uniqueColumns[0] . "'], 'unique']";
                    } elseif ($attributesCount > 1) {
                        $columnsList = implode("', '", $uniqueColumns);
                        $rules[] = "[['$columnsList'], 'unique', 'targetAttribute' => ['$columnsList']]";
                    }
                }
            }
        } catch (NotSupportedException) {
            // doesn't support unique indexes information...do nothing
        }
    }

    /**
     * Exist rules for foreign keys
     */
    private function _generateExistRules(array &$rules, TableSchema $table, Connection $db): void
    {
        foreach ($table->foreignKeys as $refs) {
            $refTable = $refs[0];
            $refTableSchema = $db->getTableSchema($refTable);
            if ($refTableSchema === null) {
                // Foreign key could point to non-existing table: https://github.com/yiisoft/yii2-gii/issues/34
                continue;
            }
            $refClassName = $this->generateClassName($refTable);
            unset($refs[0]);
            $attributes = implode("', '", array_keys($refs));
            $targetAttributes = [];
            foreach ($refs as $key => $value) {
                $targetAttributes[] = "'$key' => '$value'";
            }
            $targetAttributes = implode(', ', $targetAttributes);
            $rules[] = "[['$attributes'], 'exist', 'skipOnError' => true, 'targetClass' => $refClassName::class, 'targetAttribute' => [$targetAttributes]]";
        }
    }

    /**
     * {@inheritdoc}
     */
    final public function formView(): string
    {
        $class = new ReflectionClass(new ModelGenerator($this));

        return dirname($class->getFileName()) . '/form.php';
    }
}