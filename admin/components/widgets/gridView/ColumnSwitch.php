<?php

namespace admin\components\widgets\gridView;

use admin\widgets\input\Select2;
use common\enums\Boolean;
use common\widgets\AppActiveForm;
use kartik\editable\Editable;
use kartik\grid\{DataColumn, EditableColumn};
use kartik\popover\PopoverX;
use Yii;
use yii\base\Model;

/**
 * ColumnSwitch array widget
 *
 * Колонка для вывода данных с тумблером "да/нет"
 *
 * @package admin\components\widgets\gridView
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
final class ColumnSwitch extends ColumnWidget
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
        $filter = Boolean::indexedDescriptions();
        $column = [
            'class' => DataColumn::class,
            'attribute' => $this->attr,
            'value' => function ($model) use ($viewAttr, $valueAttr) {
                $value = $this->_getRelatedClassData($model, $viewAttr ?? $valueAttr);
                if (is_null($value)) {
                    return null;
                }
                return Boolean::from($value)->coloredDescription();
            },
            'format' => 'raw',
            'filterType' => Select2::class,
            'filterWidgetOptions' => [
                'allowClear' => true,
                'hideSearch' => true,
                'placeholder' => Yii::t('app', 'Search'),
                'data' => $filter
            ],
            'contentOptions' => [
                'style' => isset($this->width)
                    ? [
                        'max-width' => $this->width,
                        'white-space' => 'no-wrap',
                        'overflow' => 'hidden',
                        'text-overflow' => 'ellipsis'
                    ] : ['white-space' => 'nowrap']
            ],
            'headerOptions' => [
                'style' => isset($this->width)
                    ? ['min-width' => $this->width]
                    : []
            ]
        ];
        if ($this->editable) {
            $this->_addEditableOptions($column);
        }
        return $column;
    }

    /**
     * Настройка редактируемой колонки
     *
     * @param array $column Текущие настройки колонки
     */
    private function _addEditableOptions(array &$column): void
    {
        $column['class'] = EditableColumn::class;
        $column['readonly'] = $this->readonly;
        $column['editableOptions'] = function (Model $model) {
            $id = $this->_getInputUniqueId($model);
            $editableOptions = [
                'inputType' => Editable::INPUT_SWITCH,
                'closeOnBlur' => false,
                'pjaxContainerId' => $this->pjaxContainerId,
                'preHeader' => $this->preHeader,
                'placement' => PopoverX::ALIGN_AUTO,
                'formClass' => AppActiveForm::class,
                'formOptions' => ['id' => "$id-form_name", 'action' => [$this->action]],
                'options' => [
                    'id' => "$id-name",
                    'pluginOptions' => [
                        'size' => 'mini',
                        'onText' => Boolean::Yes->description(),
                        'offText' => Boolean::No->description()
                    ]
                ]
            ];
            $this->_addJsCallbackToConfig($editableOptions);
            return $editableOptions;
        };
    }
}
