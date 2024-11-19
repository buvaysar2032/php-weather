<?php

use yii\helpers\{Inflector, StringHelper};

/**
 * @var $this      yii\web\View
 * @var $generator yii\gii\generators\crud\Generator
 */

$urlParams = $generator->generateUrlParams();
$nameAttribute = $generator->getNameAttribute();

$includes = [
    admin\components\GroupedActionColumn::class,
    admin\components\widgets\gridView\Column::class,
    admin\modules\rbac\components\RbacHtml::class,
    kartik\grid\SerialColumn::class
];
if ($generator->indexWidgetType === 'grid') {
    $includes[] = admin\widgets\sortableGridView\SortableGridView::class;
    $includes[] = yii\widgets\ListView::class;
}

$hasDateColumn = false;
if (($tableSchema = $generator->getTableSchema()) === false) {
    foreach ($generator->getColumnNames() as $name) {
        if ($name === 'created_at' || $name === 'updated_at') {
            $hasDateColumn = true;
            break;
        }
    }
} else {
    foreach ($tableSchema->columns as $column) {
        if ($column->name === 'created_at' || $column->name === 'updated_at') {
            $hasDateColumn = true;
            break;
        }
    }
}
if ($hasDateColumn) {
    $includes[] = admin\components\widgets\gridView\ColumnDate::class;
}

sort($includes, SORT_NATURAL | SORT_FLAG_CASE);

echo "<?php\n";
?>

use <?= implode(";\nuse ", $includes) ?>;

/**
 * @var $this         yii\web\View
<?= !empty($generator->searchModelClass)
    ? ' * @var $searchModel  ' . ltrim($generator->searchModelClass, '\\') . "\n"
    : '' ?>
 * @var $dataProvider yii\data\ActiveDataProvider
 * @var $model        <?= ltrim($generator->modelClass, '\\') . "\n" ?>
 */

$this->title = <?= $generator->generateString(Inflector::pluralize(Inflector::camel2words(StringHelper::basename($generator->modelClass)))) ?>;
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="<?= Inflector::camel2id(StringHelper::basename($generator->modelClass)) ?>-index">

    <h1><?= '<?= ' ?>RbacHtml::encode($this->title) ?></h1>

    <div>
        <?= '<?= ' . PHP_EOL ?>
            RbacHtml::a(<?= $generator->generateString('Create ' . Inflector::camel2words(StringHelper::basename($generator->modelClass))) ?>, ['create'], ['class' => 'btn btn-success']);
//           $this->render('_create_modal', ['model' => $model]);
        ?>
    </div>

<?php if ($generator->indexWidgetType === 'grid'): ?>
    <?= '<?= ' ?>SortableGridView::widget([
        'dataProvider' => $dataProvider,
        <?= $generator->enablePjax ? "'pjax' => true,\n" : null ?>
        <?= !empty($generator->searchModelClass) ? "'filterModel' => \$searchModel,\n        'columns' => [\n" : "'columns' => [\n" ?>
            ['class' => SerialColumn::class],

<?php
$count = 0;
if (($tableSchema = $generator->getTableSchema()) === false) {
    foreach ($generator->getColumnNames() as $name) {
        $columnWidget = match ($name) {
            'id' => "Column::widget(),\n",
            'created_at', 'updated_at' => "ColumnDate::widget(['attr' => '$name', 'searchModel' => \$searchModel, 'editable' => false]),\n",
            default => "Column::widget(['attr' => '$name']),\n",
        };
        if (++$count < 6) {
            echo str_repeat(' ', 12) . $columnWidget;
        } else {
            echo '//' . str_repeat(' ', 12) . $columnWidget;
        }
    }
} else {
    foreach ($tableSchema->columns as $column) {
        $format = $generator->generateColumnFormat($column);
        $columnWidget = match ($column->name) {
            'id' => "Column::widget(),\n",
            'created_at', 'updated_at' => "ColumnDate::widget(['attr' => '$column->name', 'searchModel' => \$searchModel, 'editable' => false]),\n",
            default => "Column::widget(['attr' => '$column->name'" .
                ($format === 'text' ? '' : ", 'format' => '$format'") . "]),\n",
        };
        if (++$count < 6) {
            echo str_repeat(' ', 12) . $columnWidget;
        } else {
            echo '//' . str_repeat(' ', 12) . $columnWidget;
        }
    }
}
?>

            ['class' => GroupedActionColumn::class]
        ]
    ]) ?>
<?php else: ?>
    <?= '<?= ' ?>ListView::widget([
        'dataProvider' => $dataProvider,
        'itemOptions' => ['class' => 'item'],
        'itemView' => static fn($model, $key, $index, $widget) => RbacHtml::a(
            RbacHtml::encode($model-><?= $nameAttribute ?>),
            ['view', <?= $urlParams ?>]
        )
    ]) ?>
<?php endif ?>
</div>
