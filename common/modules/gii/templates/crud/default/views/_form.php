<?php

use yii\helpers\{Inflector, StringHelper};

/**
 * @var $this      yii\web\View
 * @var $generator yii\gii\generators\crud\Generator
 * @var $model     yii\db\ActiveRecord
 */

$model = new $generator->modelClass();
$safeAttributes = $model->safeAttributes();
if (empty($safeAttributes)) {
    $safeAttributes = $model->attributes();
}

$includes = [
    \common\widgets\AppActiveForm::class,
    kartik\icons\Icon::class,
    yii\bootstrap5\Html::class,
    yii\helpers\Url::class
];
sort($includes, SORT_NATURAL | SORT_FLAG_CASE);

echo "<?php\n";
?>

use <?= implode(";\nuse ", $includes) ?>;

/**
 * @var $this     yii\web\View
 * @var $model    <?= ltrim($generator->modelClass, '\\') . "\n" ?>
 * @var $form     AppActiveForm
 * @var $isCreate bool
 */
?>

<div class="<?= Inflector::camel2id(StringHelper::basename($generator->modelClass)) ?>-form">

    <?= '<?php ' ?>$form = AppActiveForm::begin() ?>

<?php foreach ($generator->getColumnNames() as $attribute) {
    if ($attribute !== 'created_at' && $attribute !== 'updated_at' && in_array($attribute, $safeAttributes, true)) {
        echo '    <?= ' . $generator->generateActiveField($attribute) . " ?>\n\n";
    }
} ?>
    <div class="form-group">
        <?= '<?php ' ?>if ($isCreate) {
            echo Html::submitButton(
                Icon::show('save') . Yii::t('app', 'Save And Create New'),
                ['class' => 'btn btn-success', 'formaction' => Url::to() . '?redirect=create']
            );
            echo Html::submitButton(
                Icon::show('save') . Yii::t('app', 'Save And Return To List'),
                ['class' => 'btn btn-success', 'formaction' => Url::to() . '?redirect=index']
            );
        } ?>
        <?= '<?= ' ?>Html::submitButton(Icon::show('save') . <?= $generator->generateString('Save') ?>, ['class' => 'btn btn-success']) ?>
    </div>

    <?= '<?php ' ?>AppActiveForm::end() ?>

</div>
