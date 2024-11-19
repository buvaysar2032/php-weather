<?php

use yii\helpers\{Inflector, StringHelper};

/**
 * @var $this      yii\web\View
 * @var $generator yii\gii\generators\crud\Generator
 */

$urlParams = $generator->generateUrlParams();
$modelClass = StringHelper::basename($generator->modelClass);
$modelClassName = Inflector::camel2words($modelClass);

$nameAttributeTemplate = '$model->' . $generator->getNameAttribute();
$titleTemplate = $generator->generateString('Update ' . $modelClassName . ': {name}', ['name' => '{nameAttribute}']);
if ($generator->enableI18N) {
    $title = strtr($titleTemplate, ['\'{nameAttribute}\'' => $nameAttributeTemplate]);
} else {
    $title = strtr($titleTemplate, ['{nameAttribute}\'' => '\' . ' . $nameAttributeTemplate]);
}

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

$this->title = <?= $title ?>;
$this->params['breadcrumbs'][] = [
    'label' => <?= $generator->generateString(Inflector::pluralize(Inflector::camel2words(StringHelper::basename($generator->modelClass)))) ?>,
    'url' => UserUrl::setFilters(<?= $searchModelAlias ?? $searchModelClass ?>::class)
];
$this->params['breadcrumbs'][] = ['label' => $model-><?= $generator->getNameAttribute() ?>, 'url' => ['view', <?= $urlParams ?>]];
$this->params['breadcrumbs'][] = <?= $generator->generateString('Update') ?>;
?>
<div class="<?= Inflector::camel2id(StringHelper::basename($generator->modelClass)) ?>-update">

    <h1><?= '<?= ' ?>Html::encode($this->title) ?></h1>

    <?= '<?= ' ?>$this->render('_form', ['model' => $model, 'isCreate' => false]) ?>

</div>
