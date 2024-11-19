<?php

use admin\modules\rbac\{components\RbacHtml, Module};
use admin\widgets\sortableGridView\SortableGridView;
use kartik\grid\ActionColumn;
use kartik\grid\SerialColumn;
use yii\helpers\ArrayHelper;
use yii\widgets\Pjax;

/**
 * @var $this         yii\web\View
 * @var $dataProvider yii\data\ArrayDataProvider
 * @var $searchModel  admin\modules\rbac\models\search\AuthItemSearch
 */

$labels = $this->context->getLabels();
$this->title = Yii::t(Module::MODULE_MESSAGES, $labels['Items']);
$this->params['breadcrumbs'][] = $this->title;
$this->render('/layouts/_sidebar');
?>
<div class="item-index">
    <h1><?= RbacHtml::encode($this->title) ?></h1>
    <p>
        <?= RbacHtml::a(
            Yii::t(Module::MODULE_MESSAGES, 'Create ' . $labels['Item']),
            ['create'],
            ['class' => 'btn btn-success']
        ) ?>
    </p>
    <?php Pjax::begin(['timeout' => 5000, 'enablePushState' => false]); ?>

    <?= SortableGridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => SerialColumn::class],
            [
                'attribute' => 'name',
                'label' => Yii::t(Module::MODULE_MESSAGES, 'Name')
            ],
            [
                'attribute' => 'ruleName',
                'label' => Yii::t(Module::MODULE_MESSAGES, 'Rule Name'),
                'filter' => ArrayHelper::map(Yii::$app->getAuthManager()->getRules(), 'name', 'name'),
                'filterInputOptions' => [
                    'class' => 'form-control',
                    'prompt' => Yii::t(Module::MODULE_MESSAGES, 'Select Rule')
                ]
            ],
            [
                'attribute' => 'description',
                'format' => 'ntext',
                'label' => Yii::t(Module::MODULE_MESSAGES, 'Description')
            ],
            [
                'header' => Yii::t(Module::MODULE_MESSAGES, 'Action'),
                'class' => ActionColumn::class
            ]
        ]
    ]) ?>

    <?php Pjax::end(); ?>
</div>