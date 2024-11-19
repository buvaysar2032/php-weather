<?php

use admin\modules\rbac\Module;
use common\widgets\AppActiveForm;
use yii\helpers\Html;
use yii\jui\AutoComplete;

/**
 * @var $this  yii\web\View
 * @var $model admin\modules\rbac\models\AuthItemModel
 */
?>
<div class="auth-item-form">

    <?php $form = AppActiveForm::begin(); ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => 64]) ?>

    <?= $form->field($model, 'description')->textarea(['rows' => 2]) ?>

    <?= $form->field($model, 'ruleName')->widget(AutoComplete::class, [
        'options' => ['class' => 'form-control'],
        'clientOptions' => ['source' => array_keys(Yii::$app->authManager->getRules())]
    ]) ?>

    <?php echo $form->field($model, 'data')->textarea(['rows' => 6]); ?>

    <div class="form-group">
        <?= Html::submitButton(
            $model->getIsNewRecord()
                ? Yii::t(Module::MODULE_MESSAGES, 'Create')
                : Yii::t(Module::MODULE_MESSAGES, 'Update'),
            ['class' => $model->getIsNewRecord() ? 'btn btn-success' : 'btn btn-primary']
        ) ?>
    </div>

    <?php AppActiveForm::end(); ?>
</div>
