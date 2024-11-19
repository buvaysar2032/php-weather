<?php

use admin\components\GroupedActionColumn;
use admin\components\widgets\gridView\{Column, ColumnDate};
use admin\modules\rbac\components\RbacHtml;
use admin\widgets\sortableGridView\SortableGridView;
use common\models\ExportList;
use kartik\grid\SerialColumn;

/**
 * @var $this         yii\web\View
 * @var $searchModel  common\models\ExportListSearch
 * @var $dataProvider yii\data\ActiveDataProvider
 * @var $model        common\models\ExportList
 */

$this->title = Yii::t('app', 'Export Lists');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="export-films-list-index">

    <h1><?= RbacHtml::encode($this->title) ?></h1>

    <?= SortableGridView::widget([
        'dataProvider' => $dataProvider,
        'pjax' => true,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => SerialColumn::class],

            Column::widget(),
            Column::widget(
                ['attr' => 'downloadLink', 'format' => 'raw', 'editable' => false],
                [
                    'value' => static fn (ExportList $model) => RbacHtml::a(
                        $model->downloadLabel,
                        $model->downloadLink,
                        ['class' => 'badge bg-success', 'data-pjax' => '0']
                    )
                ]
            ),
            Column::widget(['attr' => 'count', 'editable' => false]),
            ColumnDate::widget(['attr' => 'date', 'searchModel' => $searchModel, 'editable' => false]),

            ['class' => GroupedActionColumn::class, 'template' => '{view} {delete}']
        ]
    ]) ?>
</div>
