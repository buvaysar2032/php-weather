<?php

use admin\enums\AdminStatus;
use admin\widgets\input\Select2;
use common\widgets\AppActiveForm;
use kartik\icons\Icon;
use yii\bootstrap5\Html;

/**
 * @var $this  yii\web\View
 * @var $model admin\models\UserAdmin
 * @var $form  AppActiveForm
 */
?>

<div class="user-admin-form">

    <?php $form = AppActiveForm::begin() ?>
    <div class="row">
        <div class="col">
            <?= $form->field($model, 'username')->textInput(['maxlength' => true]) ?>
        </div>
        <div class="col">
            <?= $form->field($model, 'email')->textInput(['maxlength' => true]) ?>
        </div>
        <div class="col">
            <?= $form->field($model, 'status')->widget(
                Select2::class,
                ['data' => AdminStatus::indexedDescriptions(), 'hideSearch' => true]
            ) ?>
        </div>
    </div>
    <div class="form-group">
        <?= Html::submitButton(Icon::show('save') . Yii::t('app', 'Save'), ['class' => 'btn btn-success']) ?>
    </div>

    <?php AppActiveForm::end() ?>

</div>
