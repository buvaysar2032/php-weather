<?php

use common\modules\mail\Mail;
use yii\bootstrap5\Html;

/**
 * @var $this     yii\web\View
 * @var $template common\modules\mail\models\Template
 */

$this->title = Yii::t(Mail::MODULE_MESSAGES, 'Update Template: {name}', ['name' => $template->name]);
$this->params['breadcrumbs'][] = [
    'label' => Yii::t(Mail::MODULE_MESSAGES, 'Templates'),
    'url' => ['index']
];
$this->params['breadcrumbs'][] = ['label' => $template->name, 'url' => ['view', 'name' => $template->name]];
$this->params['breadcrumbs'][] = Yii::t('app', 'Update');
?>
<div class="mail-template-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', ['template' => $template]) ?>

</div>
