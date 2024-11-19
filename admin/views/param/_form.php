<?php

use admin\widgets\input\{Select2, YesNoSwitch};
use common\enums\ParamType;
use common\widgets\AppActiveForm;
use kartik\icons\Icon;
use yii\bootstrap5\Html;
use yii\helpers\Url;

/**
 * @var $this     yii\web\View
 * @var $model    common\models\Param
 * @var $form     AppActiveForm
 * @var $isCreate bool
 */
?>

<div class="param-form">

    <?php $form = AppActiveForm::begin() ?>

    <div class="row">
        <div class="col">
            <?= $form->field($model, 'group')->textInput(['maxlength' => true]) ?>
        </div>
        <div class="col">
            <?= $form->field($model, 'key')->textInput(['maxlength' => true]) ?>
        </div>
    </div>

    <?= $form->field($model, 'type')->widget(Select2::class, ['data' => ParamType::indexedDescriptions()]) ?>

    <?= $form->field($model, 'description')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'is_active')->widget(YesNoSwitch::class) ?>

    <div class="form-group">
        <?php if ($isCreate) {
            echo Html::submitButton(
                Icon::show('save') . Yii::t('app', 'Save And Create New'),
                ['class' => 'btn btn-success', 'formaction' => Url::to() . '?redirect=create']
            );
            echo Html::submitButton(
                Icon::show('save') . Yii::t('app', 'Save And Return To List'),
                ['class' => 'btn btn-success', 'formaction' => Url::to() . '?redirect=index']
            );
        } ?>
        <?= Html::submitButton(Icon::show('save') . Yii::t('app', 'Save'), ['class' => 'btn btn-success']) ?>
    </div>

    <?php AppActiveForm::end() ?>

</div>
