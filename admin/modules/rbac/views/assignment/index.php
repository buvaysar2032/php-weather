<?php

use admin\components\GroupedActionColumn;
use admin\modules\rbac\Module;
use admin\widgets\sortableGridView\SortableGridView;
use yii\helpers\{ArrayHelper, Html};
use yii\widgets\Pjax;

/**
 * @var $this            yii\web\View
 * @var $gridViewColumns array
 * @var $dataProvider    yii\data\ArrayDataProvider
 * @var $searchModel     admin\modules\rbac\models\search\AssignmentSearch
 */

$this->title = Yii::t(Module::MODULE_MESSAGES, 'Assignments');
$this->params['breadcrumbs'][] = $this->title;
$this->render('/layouts/_sidebar');
?>
<div class="assignment-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <?php Pjax::begin(['timeout' => 5000]); ?>

    <?= SortableGridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => ArrayHelper::merge($gridViewColumns, [
            [
                'class' => GroupedActionColumn::class,
                'template' => '{view}'
            ]
        ])
    ]) ?>

    <?php Pjax::end(); ?>
</div>
