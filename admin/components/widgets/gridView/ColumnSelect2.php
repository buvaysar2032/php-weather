<?php

namespace admin\components\widgets\gridView;

use admin\modules\rbac\components\RbacHtml;
use admin\widgets\input\Select2;
use common\enums\DictionaryInterface;
use common\widgets\AppActiveForm;
use kartik\editable\Editable;
use kartik\grid\{DataColumn, EditableColumn};
use kartik\popover\PopoverX;
use Yii;
use yii\base\{InvalidConfigException, Model};
use yii\helpers\StringHelper;

/**
 * ColumnSelect2 array widget
 *
 * Колонка для вывода данных с выпадающим списком
 *
 * @package admin\components\widgets\gridView
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
final class ColumnSelect2 extends ColumnWidget
{
    /**
     * Массив возможных значений или имя словаря
     */
    public string|array|DictionaryInterface $items = [];

    /**
     * Скрыть поиск среди значений
     */
    public bool $hideSearch = false;

    /**
     * Массив возможных значений для фильтрации
     */
    public array $filter = [];

    /**
     * Доступно ли пустое `null` значение колонки
     */
    public bool $nullAvailable = false;

    /**
     * Текст для пустого значения
     */
    public string $nullText = '';

    /**
     * Текст выводимый, когда в поле поиска пусто
     */
    public string $placeholder = '';

    /**
     * Конфигурация для динамического поиска через ajax запросы
     *
     * Пример:
     * ```php
     * $ajaxSearchConfig = [
     *     'url' => Url::to(['some/url']),
     *     'searchModel' => $searchModel // Необходимо для вывода текущего значения фильтра
     * ];
     * ```
     *
     * @see \admin\components\actions\ListSearchAction
     */
    public array $ajaxSearchConfig = [];

    /**
     * Путь до контроллера на внешнюю модель данных
     *
     * Например, если передать `user` то будет ссылка формата `user/view?id=`
     */
    public ?string $pathLink = null;

    /**
     * Название второго поля для отображения зависимого значения
     */
    public string|bool $viewAttr = true;

    /**
     * Формат отображения данных
     *
     * @see \common\components\UserFormatter
     */
    public string $format = 'raw';

    /**
     * Включен ли ajax поиск возможных значений
     */
    private bool $isAjaxSearch = false;

    /**
     * Берется ли список из словаря
     */
    private bool $isDictionary = false;

    public bool $multiple = false;

    /**
     * {@inheritdoc}
     * @throws InvalidConfigException
     */
    public function init(): void
    {
        if (count($this->ajaxSearchConfig)) {
            $this->isAjaxSearch = true;
            if ($this->hideSearch) {
                throw new InvalidConfigException('Can not hide search field when ajax search is active');
            }
            if (!isset($this->ajaxSearchConfig['url'])) {
                throw new InvalidConfigException('`ajaxSearchConfig => url` must be defined');
            }
            if (!isset($this->ajaxSearchConfig['searchModel'])) {
                throw new InvalidConfigException('`ajaxSearchConfig => searchModel` must be defined');
            }
        } elseif (is_string($this->items) && enum_exists($this->items)) {
            $this->isDictionary = true;
            $this->filter = $this->items::indexedDescriptions();
            $this->items = $this->items::indexedDescriptions(true);
        } elseif (count($this->items)) {
            $this->filter = $this->items;
        }
        if ($this->editable && $this->pathLink) {
            throw new InvalidConfigException('Column can not be editable and a link in same time');
        }
        if (empty($this->placeholder)) {
            $this->placeholder = Yii::t('app', 'Search');
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
            RbacHtml::addCssStyle($contentOptions, [
                'max-width' => $this->width,
                'overflow' => 'hidden',
                'text-overflow' => 'ellipsis'
            ]);
            RbacHtml::addCssStyle($headerOptions, ['width' => $this->width]);
        }
        $filterUrl = null;
        $initText = null;
        if ($this->isAjaxSearch) {
            $filterUrl = $this->ajaxSearchConfig['url'];
            $initText = $this->_getRelatedClassData($this->ajaxSearchConfig['searchModel'], $viewAttr);
            // Уменьшаем длину чтобы влезло в поле фильтра
            if (!empty($this->stringLength)) {
                $truncateLength = max(($this->stringLength - 5), 0);
                $initText = StringHelper::truncate($initText, $truncateLength);
            }
        }
        $column = [
            'class' => DataColumn::class,
            'attribute' => $this->attr,
            'value' => function (Model $model) use ($valueAttr, $viewAttr) {
                $data = $this->_getRelatedClassData($model, $valueAttr);
                $text = $this->_getRelatedClassData($model, $viewAttr);
                if ($this->pathLink) {
                    return RbacHtml::a(
                        $text,
                        ["/$this->pathLink/view", 'id' => $data],
                        ['style' => ['font-weight' => 'bold'], 'target' => '_blank', 'data' => ['pjax' => '0']]
                    );
                }
                if (count($this->items)) {
                    if ($this->multiple && is_array($data)) {
                        $items = [];
                        foreach ($data as $datum) {
                            $items[] = $this->items[$datum] ?? $this->nullText;
                        }
                        $value = implode(', ', $items);
                    } else {
                        $value = $this->items[$data] ?? $this->nullText;
                    }
                } else {
                    $value = $text ?? $data;
                }
                return $this->_truncateToTooltip((string)$value);
            },
            'contentOptions' => $contentOptions,
            'headerOptions' => $headerOptions,
            'filterType' => Select2::class,
            'filter' => $this->filter,
            'filterWidgetOptions' => [
                'allowClear' => true,
                'placeholder' => $this->placeholder,
                'data' => $this->filter,
                'url' => $filterUrl,
                'initValueText' => $initText,
                'hideSearch' => $this->hideSearch,
                'options' => ['multiple' => $this->multiple]
            ],
            'format' => $this->format
        ];
        if ($this->editable) {
            $this->_addEditableOptions($column, $valueAttr, $filterUrl);
        }
        return $column;
    }

    /**
     * Обрезать строку и завернуть в title полное значение
     */
    private function _truncateToTooltip(string $value): string
    {
        if (isset($this->width) && !$this->isDictionary) {
            $newValue = StringHelper::truncate($value, $this->stringLength);
            if (
                !empty($newValue)
                && (mb_strpos($newValue, '...', -3) !== false
                    || mb_strlen($value) > mb_strlen($newValue))
            ) { // Учитываем многоточие
                $value = RbacHtml::tag('span', $newValue, ['title' => $value, 'data' => ['toggle' => 'tooltip']]);
            }
        }
        return $value;
    }

    /**
     * Настройка редактируемой колонки
     *
     * @param array       $column    Текущие настройки колонки
     * @param string      $valueAttr Атрибут с фактическим значением поля
     * @param string|null $ajaxUrl   Ссылка на поиск значения
     */
    private function _addEditableOptions(array &$column, string $valueAttr, ?string $ajaxUrl): void
    {
        $column['class'] = EditableColumn::class;
        $column['readonly'] = $this->readonly;
        $column['editableOptions'] = function (Model $model) use ($valueAttr, $ajaxUrl) {
            $id = $this->_getInputUniqueId($model);
            $editableOptions = [
                'inputType' => Editable::INPUT_WIDGET,
                'widgetClass' => Select2::class,
                'format' => $this->format !== 'raw' ? Editable::FORMAT_BUTTON : Editable::FORMAT_LINK,
                'preHeader' => $this->preHeader,
                'options' => [
                    'id' => "$id-name",
                    'url' => $ajaxUrl,
                    'nullAvailable' => $this->nullAvailable,
                    'nullText' => $this->nullText,
                    'data' => $this->filter,
                    'pjaxContainerId' => $this->pjaxContainerId,
                    'value' => $this->_getRelatedClassData($model, $valueAttr),
                    'hideSearch' => $this->hideSearch,
                    'options' => ['multiple' => $this->multiple]
                ],
                'placement' => PopoverX::ALIGN_AUTO,
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
