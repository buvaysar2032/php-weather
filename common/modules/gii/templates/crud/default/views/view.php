<?php

use yii\helpers\{Inflector, StringHelper};

/**
 * @var $this yii\web\View
 * @var $generator \yii\gii\generators\crud\Generator
 */

$urlParams = $generator->generateUrlParams();
$modelClass = StringHelper::basename($generator->modelClass);
$searchModelClass = StringHelper::basename($generator->searchModelClass);
if ($modelClass === $searchModelClass) {
    $searchModelAlias = $searchModelClass . 'Search';
}
$includes = [
    admin\components\widgets\detailView\Column::class,
    admin\modules\rbac\components\RbacHtml::class,
    yii\widgets\DetailView::class,
    common\components\helpers\UserUrl::class
];
if (!empty($generator->searchModelClass)) {
    $includes[] = ltrim($generator->searchModelClass, '\\') .
        (isset($searchModelAlias) ? " as $searchModelAlias" : "");
}

sort($includes, SORT_NATURAL | SORT_FLAG_CASE);

echo "<?php\n";
?>

use <?= implode(";\nuse ", $includes) ?>;

/**
 * @var $this  yii\web\View
 * @var $model <?= ltrim($generator->modelClass, '\\') . "\n" ?>
 */

$this->title = $model-><?= $generator->getNameAttribute() ?>;
$this->params['breadcrumbs'][] = [
    'label' => <?= $generator->generateString(Inflector::pluralize(Inflector::camel2words(StringHelper::basename($generator->modelClass)))) ?>,
    'url' => UserUrl::setFilters(<?= $searchModelAlias ?? $searchModelClass ?>::class)
];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="<?= Inflector::camel2id(StringHelper::basename($generator->modelClass)) ?>-view">

    <h1><?= '<?= ' ?>RbacHtml::encode($this->title) ?></h1>

    <p>
        <?= '<?= '  ?>RbacHtml::a(<?= $generator->generateString('Update') ?>, ['update', <?= $urlParams ?>], ['class' => 'btn btn-primary']) ?>
        <?= '<?= '  ?>RbacHtml::a(
            <?= $generator->generateString('Delete') ?>,
            ['delete', <?= $urlParams ?>],
            [
                'class' => 'btn btn-danger',
                'data' => [
                    'confirm' => <?= $generator->generateString('Are you sure you want to delete this item?') ?>,
                    'method' => 'post'
                ]
            ]
        ) ?>
    </p>

    <?= '<?= ' ?>DetailView::widget([
        'model' => $model,
        'attributes' => [
<?php
if (($tableSchema = $generator->getTableSchema()) === false) {
    foreach ($generator->getColumnNames() as $name) {
        echo match ($name) {
            'id' => str_repeat(' ', 12) . "Column::widget(),\n",
            'created_at', 'updated_at' => str_repeat(' ', 12) .
                "Column::widget(['attr' => '$name', 'format' => 'datetime']),\n",
            default => str_repeat(' ', 12) . "Column::widget(['attr' => '$name']),\n",
        };
    }
} else {
    foreach ($generator->getTableSchema()->columns as $column) {
        $format = $generator->generateColumnFormat($column);
        echo match ($column->name) {
            'id' => str_repeat(' ', 12) . "Column::widget(),\n",
            'created_at', 'updated_at' => str_repeat(' ', 12) .
                "Column::widget(['attr' => '$column->name', 'format' => 'datetime']),\n",
            default => str_repeat(' ', 12) . "Column::widget(['attr' => '$column->name'" .
                ($format === 'text' ? '' : ", 'format' => '$format'") . "]),\n",
        };
    }
}
?>
        ]
    ]) ?>

</div>
