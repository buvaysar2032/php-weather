<?php

namespace admin\components\widgets\gridView;

use admin\widgets\input\{TimePicker};
use common\widgets\AppActiveForm;
use kartik\editable\Editable;
use kartik\grid\{DataColumn, EditableColumn};
use kartik\popover\PopoverX;
use Yii;
use yii\base\{Model};

/**
 * ColumnDate array widget
 *
 * Колонка таблицы для временных меток, с возможностью фильтровать по датам
 *
 * @package admin\components\widgets\gridView
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
final class ColumnTime extends ColumnWidget
{
    /**
     * {@inheritdoc}
     */
    public function run(): array
    {
        $vars = self::_parseAttrValue($this->attr);
        /**
         * Extracted variables
         *
         * @var string $valueAttr
         * @var bool   $isRelative
         * @var string $snakeAttr
         * @var string $viewAttr
         */
        extract($vars);
        $column = [
            'class' => DataColumn::class,
            'attribute' => $this->attr,
            'value' => function (Model $model) use ($viewAttr, $valueAttr) {
                $timestamp = $this->_getRelatedClassData($model, $viewAttr ?? $valueAttr);
                if (!$timestamp) {
                    return null;
                }
                return Yii::$app->formatter->asTime($timestamp);
            },
            'filterWidgetOptions' => [
                'pjaxContainerId' => $this->pjaxContainerId
            ]
        ];
        if ($this->editable) {
            $this->_addEditableOptions($column, $valueAttr);
        }

        return $column;
    }

    /**
     * Настройка редактируемой колонки
     *
     * @param array  $column    Текущие настройки колонки
     * @param string $valueAttr Атрибут с фактическим значением поля
     */
    private function _addEditableOptions(array &$column, string $valueAttr): void
    {
        $column['class'] = EditableColumn::class;
        $column['readonly'] = $this->readonly;
        $column['editableOptions'] = function (Model $model) use ($valueAttr) {
            $id = $this->_getInputUniqueId($model);
            Yii::$app->formatter->nullDisplay = null;
            $value = $this->_getRelatedClassData($model, $valueAttr);
            $editableOptions = [
                'inputType' => Editable::INPUT_WIDGET,
                'widgetClass' => TimePicker::class,
                'pjaxContainerId' => $this->pjaxContainerId,
                'preHeader' => $this->preHeader,
                'placement' => PopoverX::ALIGN_AUTO,
                'options' => [
                    'id' => "$id-name",
                    'options' => ['value' => $value]
                ],
                'formClass' => AppActiveForm::class,
                'formOptions' => [
                    'id' => "$id-form_name",
                    'action' => [$this->action]
                ]
            ];
            $this->_addJsCallbackToConfig($editableOptions);
            return $editableOptions;
        };
    }
}
