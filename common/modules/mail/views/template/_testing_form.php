<?php

use admin\widgets\{bootstrap\Collapse, input\AceEditor, input\Select2, tooltip\TooltipWidget};
use common\modules\mail\Mail;
use common\modules\mail\models\Template;
use common\widgets\AppActiveForm;
use yii\bootstrap5\Html;
use yii\helpers\Url;

/**
 * @var $this  yii\web\View
 * @var $form  common\widgets\AppActiveForm
 * @var $model common\modules\mail\models\TestMailing
 */

$this->title = Yii::t(Mail::MODULE_MESSAGES, 'Test Mailing');
$this->params['breadcrumbs'][] = [
    'label' => Yii::t(Mail::MODULE_MESSAGES, 'Templates'),
    'url' => ['index']
];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="mailing-testing">

    <h1><?= Html::encode($this->title) ?></h1>
    <div class="mailing-form">

        <?php $form = AppActiveForm::begin() ?>
        <div class="row">
            <div class="col-3">
                <?= $form->field($model, 'user_id')->widget(Select2::class, [
                    'url' => Url::to(['user-list']),
                    'placeholder' => Yii::t(Mail::MODULE_MESSAGES, 'Search for a user ...')
                ]) ?>
            </div>
            <div class="col-9">
                <?= $form->field($model, 'mails')
                    ->label(
                        $model->attributeLabels()['mails'] . ' ' .
                        TooltipWidget::widget([
                            'title' => Yii::t(Mail::MODULE_MESSAGES, 'List separated by commas'),
                            'fontSize' => '0.95rem'
                        ])
                    )->textInput(['maxlength' => true, 'placeholder' => 'name@example.com, name2@example.com']) ?>
            </div>
        </div>
        <div class="row">
            <div class="col-4">
                <?= $form->field($model, 'template')->widget(Select2::class, [
                    'data' => array_combine(Template::findAll(),Template::findAll()),
                    'nullAvailable' => true
                ]) ?>
            </div>
            <div class="col-3">
                <?= $form->field($model, 'mailing_count')->input('number', ['value' => 1]) ?>
            </div>
        </div>
        <?= Collapse::widget([
            'title' => Yii::t(Mail::MODULE_MESSAGES, 'Custom Template'),
            'id' => 'custom-mailing',
            'content' =>
                $form->field($model, 'mail_subject')->textInput(['maxlength' => true]) .
                $form->field($model, 'mail_text')
                    ->widget(AceEditor::class, ['mode' => 'php'])
        ]) ?>

        <div class="form-group">
            <?= Html::submitButton(Yii::t(Mail::MODULE_MESSAGES, 'Send'), ['class' => 'btn btn-success']) ?>
        </div>

        <?php AppActiveForm::end() ?>

    </div>
</div>
