<?php

use admin\modules\rbac\{components\RbacHtml, Module, RbacAsset};
use yii\helpers\Json;
use yii\widgets\DetailView;

RbacAsset::register($this);

/**
 * @var $this  yii\web\View
 * @var $model admin\modules\rbac\models\AuthItemModel
 */

$labels = $this->context->getLabels();
$this->title = Yii::t(Module::MODULE_MESSAGES, $labels['Item'] . ' : {0}', $model->name);
$this->params['breadcrumbs'][] = ['label' => Yii::t(Module::MODULE_MESSAGES, $labels['Items']), 'url' => ['index']];
$this->params['breadcrumbs'][] = $model->name;
$this->render('/layouts/_sidebar');
?>
<div class="auth-item-view">
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
            'data-method' => 'post',
        ]) ?>
        <?= RbacHtml::a(Yii::t(Module::MODULE_MESSAGES, 'Create'), ['create'], ['class' => 'btn btn-success']) ?>
    </p>
    <div class="row">
        <div class="col-sm-12">
            <?= DetailView::widget([
                'model' => $model,
                'attributes' => [
                    'name',
                    'description:ntext',
                    'ruleName',
                    'data:ntext',
                ],
            ]) ?>
        </div>
    </div>
    <?= $this->render('../_dualListBox', [
        'opts' => Json::htmlEncode(['items' => $model->getItems()]),
        'assignUrl' => ['assign', 'id' => $model->name],
        'removeUrl' => ['remove', 'id' => $model->name]
    ]) ?>
</div>
