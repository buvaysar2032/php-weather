<?php

namespace common\modules\gii\generators\crud;

use ReflectionClass;
use yii\base\Model;
use yii\bootstrap5\Html;
use yii\db\Schema;
use yii\gii\generators\crud\Generator as CrudGenerator;
use yii\helpers\{Inflector, StringHelper};

/**
 * Class Generator
 *
 * @package gii\crud
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
class Generator extends CrudGenerator
{
    /**
     * {@inheritdoc}
     */
    public function successMessage(): string
    {
        $message = parent::successMessage();
        $controllerId = $this->controllerID;
        $label = $this->generateString(
            Inflector::pluralize(Inflector::camel2words(StringHelper::basename($this->modelClass)))
        );
        $modelClass = StringHelper::basename($this->modelClass);
        $searchModelClass = StringHelper::basename($this->searchModelClass);
        if ($modelClass === $searchModelClass) {
            $searchModelAlias = $searchModelClass . 'Search';
        } else {
            $searchModelAlias = $searchModelClass;
        }
        $includes = [];
        if (!empty($this->searchModelClass)) {
            $includes[] = ltrim($this->searchModelClass, '\\') .
                (isset($searchModelAlias) ? (' as ' . $searchModelAlias) : '');
        }
        $output = <<<EOD
<p>$message</p>
<p>To access the CRUD controller, you can to add this to your application menu:</p>
EOD;
        $code = 'use ' . implode(";\nuse ", $includes) . ";\n" . <<<EOD
    ......
    [
        'label' => $label,
        'url' => UserUrl::setFilters($searchModelAlias::class, ['/$controllerId/index'])
    ],
    ......
EOD;
        return $output .
            Html::tag('pre',
                Html::tag('code', $code, ['style' => ['color' => '#000000', 'background-color' => 'unset']]),
                ['style' => ['white-space' => 'pre']]
            );
    }

    /**
     * {@inheritdoc}
     */
    final public function formView(): string
    {
        $class = new ReflectionClass(new CrudGenerator($this));

        return dirname($class->getFileName()) . '/form.php';
    }

    /**
     * {@inheritdoc}
     */
    final public function generateSearchRules(): array
    {
        if (($table = $this->getTableSchema()) === false) {
            return ["[['" . implode("', '", $this->getColumnNames()) . "'], 'safe']"];
        }
        $types = [];
        foreach ($table->columns as $column) {
            switch ($column->type) {
                case Schema::TYPE_TINYINT:
                case Schema::TYPE_SMALLINT:
                case Schema::TYPE_INTEGER:
                case Schema::TYPE_BIGINT:
                    if ($column->name === 'created_at' || $column->name === 'updated_at') {
                        $types['safe'][] = $column->name;
                    } else {
                        $types['integer'][] = $column->name;
                    }
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
                default:
                    $types['safe'][] = $column->name;
                    break;
            }
        }

        $rules = [];
        foreach ($types as $type => $columns) {
            $rules[] = "[['" . implode("', '", $columns) . "'], '$type']";
        }

        return $rules;
    }

    /**
     * {@inheritdoc}
     */
    final public function generateSearchConditions(): array
    {
        $columns = [];
        if (($table = $this->getTableSchema()) === false) {
            $class = $this->modelClass;
            /** @var Model $model */
            $model = new $class();
            foreach ($model->attributes() as $attribute) {
                $columns[$attribute] = 'unknown';
            }
        } else {
            foreach ($table->columns as $column) {
                $columns[$column->name] = $column->type;
            }
        }

        $likeConditions = [];
        $hashConditions = [];
        $likeKeyword = $this->getClassDbDriverName() === 'pgsql' ? 'ilike' : 'like';
        foreach ($columns as $column => $type) {
            // Убираем стандартные фильтры для дат
            if ($column === 'created_at' || $column === 'updated_at') {
                continue;
            }
            switch ($type) {
                case Schema::TYPE_TINYINT:
                case Schema::TYPE_SMALLINT:
                case Schema::TYPE_INTEGER:
                case Schema::TYPE_BIGINT:
                case Schema::TYPE_BOOLEAN:
                case Schema::TYPE_FLOAT:
                case Schema::TYPE_DOUBLE:
                case Schema::TYPE_DECIMAL:
                case Schema::TYPE_MONEY:
                case Schema::TYPE_DATE:
                case Schema::TYPE_TIME:
                case Schema::TYPE_DATETIME:
                case Schema::TYPE_TIMESTAMP:
                    $hashConditions[] = "'$column' => \$this->$column,";
                    break;
                default:
                    $likeConditions[] = "->andFilterWhere(['$likeKeyword', '$column', \$this->$column])";
                    break;
            }
        }

        $conditions = [];
        if (!empty($hashConditions)) {
            $conditions[] = "\$query->andFilterWhere([\n"
                . str_repeat(' ', 12) . implode("\n" . str_repeat(' ', 12), $hashConditions)
                . "\n" . str_repeat(' ', 8) . "]);\n";
        }
        if (!empty($likeConditions)) {
            $conditions[] = "\$query" . implode("\n" . str_repeat(' ', 12), $likeConditions) . ";\n";
        }

        return $conditions;
    }
}
