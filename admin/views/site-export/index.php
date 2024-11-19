<?php

use admin\models\SiteExportForm;
use admin\widgets\input\YesNoSwitch;
use common\widgets\AppActiveForm;
use kartik\icons\Icon;
use yii\bootstrap5\Html;

/**
 * @var $this  yii\web\View
 * @var $model SiteExportForm
 */

$this->title = Yii::t('app', 'Site Export');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-export-index">
    <h1><?= Html::encode($this->title) ?></h1>

    <?php $form = AppActiveForm::begin() ?>

    <div class="row">
        <div class="col">
            <?= $form->field($model, 'exportImages')->widget(YesNoSwitch::class) ?>
        </div>
        <div class="col">
            <?= $form->field($model, 'exportDb')->widget(YesNoSwitch::class) ?>
        </div>
    </div>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('app', 'Export'), ['class' => 'btn btn-success']) ?>
    </div>

    <?php AppActiveForm::end() ?>

    <?php if (file_exists($model->filename)): ?>
        <?= Html::a(
            Icon::show('download') . 'Скачать архив от ' . date('d.m.Y H:i'),
            ['site-export/download', 'token' => Yii::$app->request->get('token')],
            ['class' => 'btn btn-primary']
        ) ?>
    <?php endif ?>
</div>
