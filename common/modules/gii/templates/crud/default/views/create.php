<?php

use yii\helpers\{Inflector, StringHelper};

/**
 * @var $this      yii\web\View
 * @var $generator yii\gii\generators\crud\Generator
 */

$modelClass = StringHelper::basename($generator->modelClass);
$searchModelClass = StringHelper::basename($generator->searchModelClass);
if ($modelClass === $searchModelClass) {
    $searchModelAlias = $searchModelClass . 'Search';
}
$includes = [
    common\components\helpers\UserUrl::class,
    yii\bootstrap5\Html::class
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

$this->title = <?= $generator->generateString('Create ' . Inflector::camel2words(StringHelper::basename($generator->modelClass))) ?>;
$this->params['breadcrumbs'][] = [
    'label' => <?= $generator->generateString(Inflector::pluralize(Inflector::camel2words(StringHelper::basename($generator->modelClass)))) ?>,
    'url' => UserUrl::setFilters(<?= $searchModelAlias ?? $searchModelClass ?>::class)
];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="<?= Inflector::camel2id(StringHelper::basename($generator->modelClass)) ?>-create">

    <h1><?= '<?= ' ?>Html::encode($this->title) ?></h1>

    <?= '<?= ' ?>$this->render('_form', ['model' => $model, 'isCreate' => true]) ?>

</div>
