<?php

use admin\widgets\input\AceEditor;
use common\widgets\AppActiveForm;
use kartik\icons\Icon;
use yii\bootstrap5\Html;

/**
 * @var $this     yii\web\View
 * @var $template common\modules\mail\models\Template
 * @var $form     AppActiveForm
 */
?>

<div class="mail-template-form">

    <?php $form = AppActiveForm::begin() ?>

    <?= $form->field($template, 'name')->textInput(['maxlength' => true]) ?>

    <div style="text-align: center">
        <mail-preview
            ace-layout-style="aceLayoutStyle"
            layout-style-input="layout-style-input"
            ace-layout="aceLayout"
            layout-input="layout-input"
            ace-content="aceContent"
            content-input="content-input"
            ace-style="aceStyle"
            style-input="style-input"
            render-url="/admin/mail/template/render-pug"
        ></mail-preview>
    </div>
    <div class="row">
        <div class="col">
            <?= $form->field($template, 'pugLayout', ['inputOptions' => ['id' => 'layout-input']])
                ->widget(AceEditor::class, ['id' => 'aceLayout', 'mode' => 'jade']) ?>
        </div>
        <div class="col">
            <?= $form->field($template, 'layoutStyle', ['inputOptions' => ['id' => 'layout-style-input']])
                ->widget(AceEditor::class, ['id' => 'aceLayoutStyle', 'mode' => 'css']) ?>
        </div>
    </div>
    <div class="row">
        <div class="col">
            <?= $form->field($template, 'pugHtml', ['inputOptions' => ['id' => 'content-input']])
                ->widget(AceEditor::class, ['id' => 'aceContent', 'mode' => 'jade']) ?>
        </div>
        <div class="col">
            <?= $form->field($template, 'style', ['inputOptions' => ['id' => 'style-input']])
                ->widget(AceEditor::class, ['id' => 'aceStyle', 'mode' => 'css']) ?>
        </div>
    </div>
    <?= $form->field($template, 'text')->widget(AceEditor::class, ['mode' => 'php']) ?>

    <div class="form-group">
        <?= Html::submitButton(
            Icon::show('save') . Yii::t('app', 'Save'),
            ['class' => 'btn btn-success']
        ) ?>
    </div>

    <?php AppActiveForm::end() ?>

</div>
