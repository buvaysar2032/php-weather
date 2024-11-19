<?php

namespace admin\components\widgets\gridView;

use admin\widgets\ckfinder\CKFinderInputFile;
use admin\widgets\input\{DatePicker, DateTimePicker, Select2, TimePicker, YesNoSwitch};
use Closure;
use common\widgets\AppActiveForm;
use kartik\base\Config;
use kartik\color\ColorInput;
use kartik\editable\Editable;
use kartik\grid\{DataColumn, EditableColumn};
use kartik\popover\PopoverX;
use Yii;
use yii\base\{InvalidConfigException, Model};
use yii\bootstrap5\Html;
use yii\helpers\StringHelper;

/**
 * Column array widget
 *
 * Обычная колонка таблицы
 *
 * @package admin\components\widgets\gridView
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
final class Column extends ColumnWidget
{
    /**
     * {@inheritdoc}
     */
    public string $attr = 'id';

    public ?string $viewAttr = null;

    /**
     * Формат отображения данных
     *
     * @see \common\components\UserFormatter
     */
    public string $format = 'text';

    /**
     * Тип поля ввода.
     *
     * Возможные значения Editable::INPUT_TEXT, Editable::INPUT_TEXTAREA, Editable::INPUT_NUMBER
     * Для других типов ввода использовать другие виджеты
     */
    public string|Closure $type = Editable::INPUT_TEXT;

    /**
     * {@inheritdoc}
     */
    public function init(): void
    {
        if ($this->attr === 'id') {
            // Авто-запрет на редактирование главного ключа
            $this->editable = false;
            // Стандартная ширина, если не задана
            if (!isset($this->width)) {
                $this->width = 20;
            }
        }
        parent::init();
    }

    /**
     * {@inheritdoc}
     */
    public function run(): array
    {
        $vars = self::_parseAttrValue($this->attr, $this->viewAttr);
        /**
         * Extracted variables
         *
         * @var string $valueAttr
         * @var bool   $isRelative
         * @var string $snakeAttr
         * @var string $viewAttr
         */
        extract($vars);
        $contentOptions = ['style' => ['white-space' => 'nowrap']];
        $headerOptions = [];
        if (isset($this->width)) {
            Html::addCssStyle($contentOptions, [
                'max-width' => $this->width,
                'overflow' => 'hidden',
                'text-overflow' => 'ellipsis'
            ]);
            Html::addCssStyle($headerOptions, ['width' => $this->width]);
        }
        $column = [
            'class' => DataColumn::class,
            'attribute' => $this->attr,
            'value' => fn($model) => $this->_getRelatedClassData($model, $viewAttr ?? $valueAttr),
            'format' => $this->format,
            'filterInputOptions' => ['class' => 'form-control', 'placeholder' => Yii::t('app', 'Search')],
            'contentOptions' => $contentOptions,
            'headerOptions' => $headerOptions
        ];
        if ($this->editable) {
            $this->_addEditableOptions($column, $valueAttr, $viewAttr);
        }
        return $column;
    }

    /**
     * Настройка редактируемой колонки
     *
     * @param array  $column    Текущие настройки колонки
     * @param string $valueAttr Атрибут с фактическим значением поля
     */
    private function _addEditableOptions(array &$column, string $valueAttr, ?string $viewAttr = null): void
    {
        $column['class'] = EditableColumn::class;
        $column['readonly'] = $this->readonly;
        $column['editableOptions'] = function (Model $model) use ($valueAttr, $viewAttr) {
            $id = $this->_getInputUniqueId($model);
            //Отключаем событие на нажатие кнопки Enter, если это textarea, для работы переноса строк
            if ($this->type === Editable::INPUT_TEXTAREA) {
                Yii::$app->view->registerJs(
                    <<<JS
jQuery(function($) { // DOM ready
  let form = $('form#{$id}_form_name');
  form.off('keyup');
});
JS
                );
            }
            $vars = $this->_initCustomWidgetClass($model);
            /**
             * @var string $widgetClass
             * @var array  $options
             * @var string $inputType
             * @var array  $editableOptions
             */
            extract($vars);

            $editableOptions = array_merge_recursive($editableOptions, [
                'inputType' => $inputType,
                'widgetClass' => $widgetClass,
                'format' => $this->format !== 'text' ? Editable::FORMAT_BUTTON : Editable::FORMAT_LINK,
                'editableButtonOptions' => $this->format !== 'text'
                    ? ['class' => 'btn btn-sm btn-outline-secondary editable-button']
                    : null,
                'preHeader' => $this->preHeader,
                'pjaxContainerId' => $this->pjaxContainerId,
                'formClass' => AppActiveForm::class,
                'formOptions' => ['id' => "$id-form_name", 'action' => [$this->action]],
                'options' => array_merge_recursive($options, ['id' => "$id-name"])
            ]);
            $this->_addJsCallbackToConfig($editableOptions);
            if (
                ($this->format === 'ntext' ||
                    $this->format === 'html' ||
                    $this->type === Editable::INPUT_TEXTAREA
                ) &&
                $widgetClass !== YesNoSwitch::class
            ) {
                $editableOptions['size'] = PopoverX::SIZE_LARGE;
                $editableOptions['placement'] = PopoverX::ALIGN_AUTO;
            }
            $editableOptions['displayValue'] = $this->_getRelatedClassData($model, $viewAttr ?? $valueAttr);
            if (
                isset($this->width)
                && !in_array($widgetClass, [
                    YesNoSwitch::class,
                    Select2::class,
                    ColorInput::class,
                    CKFinderInputFile::class,
                    DatePicker::class,
                    DateTimePicker::class,
                    TimePicker::class
                ])
            ) {
                $editableOptions['displayValue'] = StringHelper::truncate(
                    $editableOptions['displayValue'],
                    $this->stringLength
                );
                if ($this->format === 'url') {
                    $editableOptions['displayValue'] = Html::a($editableOptions['displayValue'], $value);
                }
            } else {
                $editableOptions['displayValue'] = Yii::$app->formatter->format(
                    $editableOptions['displayValue'],
                    $this->format
                );
            }
            return $editableOptions;
        };
    }

    /**
     * Определение иного типа редактируемого поля
     *
     * @throws InvalidConfigException
     */
    private function _initCustomWidgetClass(Model $model): array
    {
        $widgetClass = null;
        $options = [];
        $editableOptions = [];
        $inputType = $this->type;
        if (is_callable($inputType)) {
            $res = $inputType($model, $this->attr);
            if (is_array($res)) {
                if ($widgetClass = $res['class'] ?? null) {
                    $options = $res;
                    unset($options['class']);
                } elseif ($inputType = $res['inputType']) {
                    $editableOptions = $res;
                    unset($editableOptions['inputType']);
                } else {
                    throw new InvalidConfigException('`class` or `inputType` must be returned by `type` callback');
                }
            } elseif (Config::isValidInput($res)) {
                $inputType = $res;
            } else {
                $widgetClass = $res;
            }
            if ($widgetClass !== null) {
                $inputType = Editable::INPUT_WIDGET;
            } elseif (empty($inputType)) {
                $inputType = Editable::INPUT_TEXTAREA;
            }
        }
        return compact('widgetClass', 'options', 'inputType', 'editableOptions');
    }
}
