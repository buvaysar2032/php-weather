<?php

namespace admin\widgets\sortableGridView;

use admin\widgets\pagination\LinkPagerExt;
use Closure;
use kartik\grid\GridView;
use Yii;
use yii\bootstrap5\Html;
use yii\db\ActiveRecord;
use yii\grid\{Column, GridViewAsset};
use yii\helpers\Json;

/**
 * Сортируемый GridView, Если задать sortUrl, то отключится сортировка, и станет доступно перетаскивание строк таблицы
 *
 * @package admin\widgets\sortableGridView
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
class SortableGridView extends GridView
{
    /**
     * (required) The URL of related SortableAction
     *
     * @see SortableAction
     */
    public string $sortUrl;

    /**
     * (optional) The text shown in the model while the server is reordering model
     * You can use HTML-tag in this attribute.
     */
    public string $sortingPromptText;

    /**
     * (optional) The text shown in alert box when sorting failed.
     */
    public string $failText;

    /**
     * Использовать ли расширенный пагинатор
     */
    public bool $useExtPager = false;

    /**
     * Настройки Pjax по умолчанию
     */
    public $pjaxSettings = ['options' => ['id' => 'grid-view']];

    /**
     * {@inheritdoc}
     */
    public function init(): void
    {
        parent::init();
        $this->export = false;
        if (!isset($this->sortingPromptText)) {
            $this->sortingPromptText = Yii::t('app', 'Loading...');
        }
        if (!isset($this->failText)) {
            $this->failText = Yii::t('app', 'Fail to sort');
        }
        if (isset($this->sortUrl)) {
            GridViewAsset::register($this->view);
            SortableGridViewAsset::register($this->view);
            Html::addCssClass($this->tableOptions, 'sortable-grid-view');
        }
        if ($this->filterModel instanceof ActiveRecord && !$this->dataProvider->getTotalCount()) {
            $founded = false;
            /* @var ActiveRecord $modelClass */
            foreach ($this->filterModel as $value) {
                if ($value !== null && $value !== '') {
                    $founded = true;
                    $modelClass = $this->filterModel::class;
                    if (!$modelClass::find()->count()) {
                        $this->filterModel = null;
                    }
                    break;
                }
            }
            if (!$founded) {
                $this->filterModel = null;
            }
        }
        if ($this->useExtPager) {
            $this->pager = ['class' => LinkPagerExt::class];
        }
    }

    /**
     * {@inheritdoc}
     * @see \yii\grid\GridView::renderTableRow()
     */
    final public function renderTableRow($model, $key, $index): string
    {
        if (isset($this->sortUrl)) {
            $cells = [];
            foreach ($this->columns as $column) {
                /** @var Column $column */
                $cells[] = $column->renderDataCell($model, $key, $index);
            }

            if ($this->rowOptions instanceof Closure) {
                $options = call_user_func($this->rowOptions, $model, $key, $index, $this);
            } else {
                $options = $this->rowOptions;
            }

            $options['data-key'] = is_array($key) ? Json::encode($key) : (string)$key;

            return Html::tag('tr', implode('', $cells), $options);
        }
        return parent::renderTableRow($model, $key, $index);
    }

    /**
     * {@inheritdoc}
     */
    final public function run(): void
    {
        if (isset($this->sortUrl)) {
            foreach ($this->columns as $column) {
                if (property_exists($column, 'enableSorting')) {
                    $column->enableSorting = false;
                }
            }
        }
        parent::run();
        if (isset($this->sortUrl)) {
            $options = [
                'id' => $this->id,
                'action' => $this->sortUrl,
                'sortingPromptText' => $this->sortingPromptText,
                'sortingFailText' => $this->failText,
                'csrfTokenName' => Yii::$app->request->csrfParam,
                'csrfToken' => Yii::$app->request->csrfToken
            ];
            $options = Json::htmlEncode($options);
            $this->view->registerJs('jQuery.SortableGridView(' . $options . ');');
        }
    }
}
