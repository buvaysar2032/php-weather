<?php

use admin\modules\rbac\Module;
use yii\helpers\Html;

/**
 * @var $this  yii\web\View
 * @var $model admin\modules\rbac\models\AuthItemModel
 */

$context = $this->context;
$labels = $this->context->getLabels();
$this->title = Yii::t(Module::MODULE_MESSAGES, 'Update ' . $labels['Item'] . ' : {0}', $model->name);
$this->params['breadcrumbs'][] = ['label' => Yii::t(Module::MODULE_MESSAGES, $labels['Items']), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->name]];
$this->params['breadcrumbs'][] = Yii::t(Module::MODULE_MESSAGES, 'Update');
$this->render('/layouts/_sidebar');
?>
<div class="auth-item-update">
    <h1><?= Html::encode($this->title) ?></h1>
    <?= $this->render('_form', ['model' => $model]) ?>
</div>