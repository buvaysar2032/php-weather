<?php

namespace admin\components\widgets\gridView;

use admin\widgets\input\{DatePicker, DateTimePicker};
use common\widgets\AppActiveForm;
use kartik\editable\Editable;
use kartik\grid\{DataColumn, EditableColumn, GridViewInterface};
use kartik\popover\PopoverX;
use Yii;
use yii\base\{InvalidConfigException, Model};
use yii\db\BaseActiveRecord;
use yii\web\JsExpression;

/**
 * ColumnDate array widget
 *
 * Колонка таблицы для временных меток, с возможностью фильтровать по датам
 *
 * @package admin\components\widgets\gridView
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
final class ColumnDate extends ColumnWidget
{
    /**
     * Search модель
     */
    public BaseActiveRecord $searchModel;

    /**
     * Выводить время
     *
     * Если false, то будет выводить и фильтровать только по дате
     */
    public bool $withTime = true;

    /**
     * {@inheritdoc}
     * @throws InvalidConfigException
     */
    public function init(): void
    {
        if (!isset($this->searchModel)) {
            throw new InvalidConfigException('`searchModel` is not defined');
        }
        parent::init();
    }

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
                if ($this->withTime) {
                    return Yii::$app->formatter->asDatetime($timestamp);
                }
                return Yii::$app->formatter->asDate($timestamp);
            },
            'filterType' => GridViewInterface::FILTER_DATE_RANGE,
            'filterWidgetOptions' => [
                'model' => $this->searchModel,
                'attribute' => $this->attr,
                'pjaxContainerId' => $this->pjaxContainerId,
                'presetDropdown' => true,
                'pluginOptions' => [
                    'timePicker' => $this->withTime,
                    'timePicker24Hour' => true,
                    'locale' => ['format' => $this->withTime ? 'DD.MM.Y HH:mm' : 'DD.MM.Y']
                ],
                'pluginEvents' => [
                    'cancel.daterangepicker' => new JsExpression(
                        // Обработка события кнопки "отмена", зачистка поля фильтра и применение фильтров в таблице
                        "function(ev, picker) {let e13=$.Event('keydown');e13.keyCode=13;let _input=$(this);if(!$(this).is('input')){_input=$(this).parent().find('input:hidden');}_input.val('').trigger(e13);}"
                    )
                ]
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
                'widgetClass' => $this->withTime ? DateTimePicker::class : DatePicker::class,
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
