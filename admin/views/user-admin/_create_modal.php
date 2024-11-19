<?php

use admin\modules\rbac\components\RbacHtml;
use common\widgets\AppActiveForm;
use kartik\icons\Icon;
use yii\bootstrap5\{Html, Modal};

/**
 * @var $this  yii\web\View
 * @var $model admin\models\AdminSignupForm
 * @var $form  AppActiveForm
 */
?>

<?php $modal = Modal::begin([
    'title' => Yii::t('app', 'New User Admin'),
    'toggleButton' => [
        'label' => Yii::t('app', 'Create User Admin'),
        'class' => 'btn btn-success',
        'disabled' => !RbacHtml::isAvailable(['admin-signup'])
    ]
]) ?>

<div class="user-form">
    <?php $form = AppActiveForm::begin() ?>

    <?= $form->field($model, 'email')->textInput(['maxlength' => true, 'placeholder' => 'name@example.com']) ?>

    <?= $form->field($model, 'username')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'password')->passwordInput(['maxlength' => true]) ?>

    <div class="form-group">
        <?= Html::submitButton(Icon::show('save') . Yii::t('app', 'Save'), ['class' => 'btn btn-success']) ?>
    </div>

    <?php AppActiveForm::end() ?>

</div>

<?php Modal::end() ?>
