<?php

namespace common\models\traits;

use ReflectionClass;
use ReflectionNamedType;
use ReflectionType;
use ReflectionUnionType;
use yii\db\{ActiveRecord, ColumnSchema};

/**
 * Trait SetTypedAttributes
 *
 * @package common\components
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
trait SetTypedAttributesTrait
{
    /**
     * Sets the attribute values in a massive way.
     *
     * @param array $values   attribute values (name => value) to be assigned to the model.
     * @param bool  $safeOnly whether the assignments should only be done to the safe attributes.
     *                        A safe attribute is one that is associated with a validation rule in the current [[scenario]].
     *
     * @see attributes()
     * @see safeAttributes()
     */
    public function setAttributes($values, $safeOnly = true): void
    {
        if (is_array($values)) {
            $attributes = array_flip($safeOnly ? $this->safeAttributes() : $this->attributes());
            $reflection = new ReflectionClass(static::class);
            $isSearchModel = str_ends_with(static::class, 'Search');
            $isActiveRecord = in_array(ActiveRecord::class, class_parents(static::class), true);
            foreach ($values as $name => $value) {
                if (isset($attributes[$name])) {
                    if ($reflection->hasProperty($name) && ($type = $reflection->getProperty($name)?->getType())) {
                        $this->_setTypedAttribute($type, $name, $value);
                    } elseif (
                        $value !== ''
                        && !$isSearchModel
                        && $isActiveRecord
                        && ($column = static::getTableSchema()->columns[$name] ?? null)
                    ) {
                        $this->_setActiveRecordAttribute($column, $name, $value);
                    } elseif ($value === '' && $isSearchModel) {
                        // Set empty search filter
                        $this->$name = null;
                    } else {
                        // Default set
                        $this->$name = $value;
                    }
                } elseif ($safeOnly) {
                    $this->onUnsafeAttribute($name, $value);
                }
            }
        }
    }

    private function _setTypedAttribute(ReflectionType $type, string $name, mixed $value): void
    {
        if ($type instanceof ReflectionUnionType) {
            $types = $this->_unionTypeToArray($type);
            if (is_array($value) && in_array('array', $types, true)) {
                foreach ($value as &$item) {
                    if (in_array('float', $types, true)) {
                        $item = is_numeric($item) ? (float)$item : null;
                    } elseif (in_array('int', $types, true)) {
                        $item = is_numeric($item) ? (int)$item : null;
                    }
                }
                unset($item);
                $this->$name = $value;
            } elseif (in_array('bool', $types, true)) {
                $this->$name = (bool)$value;
            } elseif (in_array('float', $types, true)) {
                $this->$name = is_numeric($value) ? (float)$value : null;
            } elseif (in_array('int', $types, true)) {
                $this->$name = is_numeric($value) ? (int)$value : null;
            } else {
                $this->$name = $value;
            }
        } elseif ($type instanceof ReflectionNamedType) {
            $this->$name = match ($type->getName()) {
                'bool' => (bool)$value,
                'int' => is_numeric($value) ? (int)$value : null,
                'float' => is_numeric($value)
                    ? (float)(is_string($value) ? str_replace(',', '.', $value) : $value)
                    : null,
                default => $value,
            };
        }
    }

    private function _setActiveRecordAttribute(ColumnSchema $column, string $name, mixed $value): void
    {
        if ($column->type === 'tinyint') {
            if ($value === 'true') {
                $value = 1;
            }
            if ($value === 'false') {
                $value = 0;
            }
        }
        // DBSchema dynamic property
        $this->$name = match ($column->type) {
            'boolean' => (bool)$value,
            'smallint', 'integer', 'bigint', 'timestamp' => (int)$value,
            'float', 'decimal', 'money' => (float)(is_string($value)
                ? str_replace(',', '.', $value)
                : $value),
            default => $value,
        };
    }

    private function _unionTypeToArray(ReflectionUnionType $unionType): array
    {
        $types = $unionType->getTypes();
        foreach ($types as &$type) {
            $type = $type->getName();
        }
        return $types;
    }
}
