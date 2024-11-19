<?php

use yii\helpers\{Inflector, StringHelper};

/**
 * @var $this      yii\web\View
 * @var $generator yii\gii\generators\crud\Generator
 */
$includes = [
    admin\modules\rbac\components\RbacHtml::class,
    yii\bootstrap5\Modal::class,
];
$name = Inflector::camel2words(StringHelper::basename($generator->modelClass));

echo "<?php\n";
?>

use <?= implode(";\nuse ", $includes) ?>;

/**
 * @var $this  yii\web\View
 * @var $model <?= ltrim($generator->modelClass, '\\') . "\n" ?>
 */
?>

<?= '<?php ' ?>$modal = Modal::begin([
    'title' => Yii::t('app', 'New <?= $name ?>'),
    'toggleButton' => [
        'label' => Yii::t('app', 'Create <?= $name ?>'),
        'class' => 'btn btn-success',
        'disabled' => !RbacHtml::isAvailable(['create'])
    ]
]) ?>

<?= '<?= ' ?>$this->render('_form', ['model' => $model, 'isCreate' => false]) ?>

<?= '<?php ' ?>Modal::end() ?>
