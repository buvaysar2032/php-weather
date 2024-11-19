<?php

use admin\modules\rbac\{components\RbacHtml, Module};
use yii\widgets\DetailView;

/**
 * @var $this  yii\web\View
 * @var $model admin\modules\rbac\models\BizRuleModel
 */

$this->title = Yii::t(Module::MODULE_MESSAGES, 'Rule : {0}', $model->name);
$this->params['breadcrumbs'][] = ['label' => Yii::t(Module::MODULE_MESSAGES, 'Rules'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $model->name;
$this->render('/layouts/_sidebar');
?>
<div class="rule-item-view">

    <h1><?= RbacHtml::encode($this->title) ?></h1>

    <p>
        <?= RbacHtml::a(
            Yii::t(Module::MODULE_MESSAGES, 'Update'),
            ['update', 'id' => $model->name],
            ['class' => 'btn btn-primary']
        ) ?>
        <?= RbacHtml::a(Yii::t(Module::MODULE_MESSAGES, 'Delete'), ['delete', 'id' => $model->name], [
            'class' => 'btn btn-danger',
            'data-confirm' => Yii::t(Module::MODULE_MESSAGES, 'Are you sure to delete this item?'),
            'data-method' => 'post'
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'name',
            'className'
        ]
    ]) ?>

</div>
