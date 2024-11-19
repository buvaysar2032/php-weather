<?php

namespace admin\components;

use admin\modules\rbac\components\RbacHtml;
use Closure;
use common\components\helpers\UserUrl;
use Exception;
use kartik\grid\{DataColumn, GridViewInterface};
use kartik\icons\Icon;
use Yii;
use yii\base\InvalidConfigException;
use yii\helpers\Url;

/**
 * Класс GroupedActionColumn
 *
 * Переделанный класс ActionColumn для работы группировки строчек
 *
 * @package admin\components
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
final class GroupedActionColumn extends DataColumn
{
    /**
     * {@inheritdoc}
     */
    public $filterOptions = [
        'class' => 'kv-align-center kv-align-middle'
    ];

    /**
     * The ID of the controller that should handle the actions specified here.
     *
     * If not set, it will use the currently active controller. This property is mainly used by
     * [[urlCreator]] to create URLs for different actions. The value of this property will be prefixed
     * to each action name to form the route of the action.
     */
    public string $controller;

    public string $template = '{view} {update} {delete}';

    /**
     * Button rendering callbacks. The array keys are the button names (without curly brackets),
     * and the values are the corresponding button rendering callbacks.
     *
     * The callbacks should use the following signature:
     * ```php
     * function ($url, $model, $key) {
     *     // return the button HTML code
     * }
     * ```
     * where `$url` is the URL that the column creates for the button, `$model` is the model object
     * being rendered for the current row, and `$key` is the key of the model in the data provider array.
     * You can add further conditions to the button, for example only display it, when the model is
     * editable (here assuming you have a status field that indicates that):
     * ```php
     * [
     *     'update' => function ($url, $model, $key) {
     *         return $model->status === 'editable' ? Html::a('Update', $url) : '';
     *     },
     * ],
     * ```
     *
     * @var Closure[]
     */
    public array $buttons = [];

    /**
     * Visibility conditions for each button. The array keys are the button names (without curly brackets),
     * and the values are the boolean true/false or the anonymous function. When the button name is not specified in
     * this array it will be shown by default.
     *
     * The callbacks must use the following signature:
     * ```php
     * function ($model, $key, $index) {
     *     return $model->status === 'editable';
     * }
     * ```
     * Or you can pass a boolean value:
     * ```php
     * [
     *     'update' => \Yii::$app->user->can('update'),
     * ],
     * ```
     *
     * @var bool[]|Closure[]
     */
    public array $visibleButtons = [];

    /**
     * A callback that creates a button URL using the specified model information.
     *
     * The signature of the callback should be the same as that of [[createUrl()]]
     * Since Yii 2.0.10 it can accept additional parameter, which refers to the column instance itself:
     * ```php
     * function (string $action, mixed $model, mixed $key, integer $index, ActionColumn $this) {
     *     //return string;
     * }
     * ```
     * If this property is not set, button URLs will be created using [[createUrl()]].
     */
    public Closure $urlCreator;

    /**
     * Html options to be applied to the [[initDefaultButton()|default button]].
     */
    public array $buttonOptions = [];

    /**
     * {@inheritdoc}
     */
    public function init(): void
    {
        $this->initColumnSettings([
            'hiddenFromExport' => true,
            'mergeHeader' => !isset($this->grid->filterModel),
            'hAlign' => GridViewInterface::ALIGN_CENTER,
            'vAlign' => GridViewInterface::ALIGN_MIDDLE,
            'width' => '50px'
        ]);
        if (!isset($this->header)) {
            $this->header = Yii::t('kvgrid', 'Actions');
        }
        parent::init();
        $this->initDefaultButtons();
    }

    /**
     * Initializes the default button rendering callbacks.
     */
    private function initDefaultButtons(): void
    {
        $this->initDefaultButton('view', 'eye', ['class' => 'text-info']);
        $this->initDefaultButton('update', 'pencil-alt', ['class' => 'text-primary']);
        $this->initDefaultButton('delete', 'trash-alt', [
            'class' => 'text-danger',
            'data-confirm' => Yii::t('yii', 'Are you sure you want to delete this item?'),
            'data-method' => 'post'
        ]);
    }

    /**
     * Initializes the default button rendering callback for single button.
     *
     * @param string $name              Button name, as it's written in template
     * @param string $iconName          The part of Bootstrap glyphicon class that makes it unique
     * @param array  $additionalOptions Array of additional options
     *
     * @since 2.0.11
     */
    private function initDefaultButton(string $name, string $iconName, array $additionalOptions = []): void
    {
        if (!isset($this->buttons[$name]) && str_contains($this->template, '{' . $name . '}')) {
            $this->buttons[$name] = function ($url) use ($name, $iconName, $additionalOptions) {
                $title = match ($name) {
                    'view' => Yii::t('yii', 'View'),
                    'update' => Yii::t('yii', 'Update'),
                    'delete' => Yii::t('yii', 'Delete'),
                    default => ucfirst($name),
                };
                $options = array_merge([
                    'title' => $title,
                    'data-bs-toggle' => 'tooltip',
                    'aria-label' => $title,
                    'data-pjax' => '0'
                ], $additionalOptions, $this->buttonOptions);
                return RbacHtml::a(Icon::show($iconName), $url, $options, true);
            };
        }
    }

    /**
     * Рендер кнопки для сброса всех фильтров
     *
     * @throws InvalidConfigException
     * @throws Exception
     */
    public function renderFilterCellContent(): string
    {
        return isset($this->grid->filterModel)
            ? RbacHtml::a(Icon::show('redo'), UserUrl::clearFilters($this->grid->filterModel::class), [
                'class' => 'text-primary',
                'title' => Yii::t('app', 'Clear Filters'),
                'data-bs-toggle' => 'tooltip'
            ])
            : $this->grid->emptyCell;
    }

    /**
     * {@inheritdoc}
     */
    public function renderDataCellContent($model, $key, $index): array|string|null
    {
        return preg_replace_callback(
            '/{([\w\-\/]+)}/',
            function ($matches) use ($model, $key, $index) {
                $name = $matches[1];

                if (isset($this->visibleButtons[$name])) {
                    $isVisible = $this->visibleButtons[$name] instanceof Closure
                        ? call_user_func($this->visibleButtons[$name], $model, $key, $index)
                        : $this->visibleButtons[$name];
                } else {
                    $isVisible = true;
                }

                if ($isVisible && isset($this->buttons[$name])) {
                    $url = $this->createUrl($name, $model, $key, $index);
                    return call_user_func($this->buttons[$name], $url, $model, $key);
                }

                return '';
            },
            $this->template
        );
    }

    /**
     * Creates a URL for the given action and model.
     * This method is called for each button and each row.
     *
     * @param string $action the button name (or action ID)
     * @param mixed  $model  the data
     * @param mixed  $key    the key associated with the data model
     * @param int    $index  the current row index
     *
     * @return string the created URL
     */
    public function createUrl(string $action, mixed $model, mixed $key, int $index): string
    {
        if (isset($this->urlCreator) && is_callable($this->urlCreator)) {
            return call_user_func($this->urlCreator, $action, $model, $key, $index, $this);
        }

        $params = is_array($key) ? $key : ['id' => (string)$key];
        $params[0] = isset($this->controller) ? "$this->controller/$action" : $action;

        return Url::toRoute($params);
    }
}
