<?php

use admin\components\GroupedActionColumn;
use admin\modules\rbac\{components\RbacHtml, Module};
use admin\widgets\sortableGridView\SortableGridView;
use kartik\grid\SerialColumn;
use yii\widgets\Pjax;

/**
 * @var $this         yii\web\View
 * @var $dataProvider yii\data\ArrayDataProvider
 * @var $searchModel  admin\modules\rbac\models\search\BizRuleSearch
 */

$this->title = Yii::t(Module::MODULE_MESSAGES, 'Rules');
$this->params['breadcrumbs'][] = $this->title;
$this->render('/layouts/_sidebar');
?>
<div class="role-index">

    <h1><?= RbacHtml::encode($this->title) ?></h1>

    <p>
        <?= RbacHtml::a(Yii::t(Module::MODULE_MESSAGES, 'Create Rule'), ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php Pjax::begin(['timeout' => 5000]); ?>

    <?= SortableGridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => SerialColumn::class],
            [
                'attribute' => 'name',
                'label' => Yii::t(Module::MODULE_MESSAGES, 'Name'),
            ],
            [
                'header' => Yii::t(Module::MODULE_MESSAGES, 'Action'),
                'class' => GroupedActionColumn::class
            ]
        ]
    ]) ?>

    <?php Pjax::end(); ?>
</div>
