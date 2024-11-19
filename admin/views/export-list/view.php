<?php

use admin\components\widgets\detailView\Column;
use admin\modules\rbac\components\RbacHtml;
use common\components\helpers\UserUrl;
use common\models\{ExportList, ExportListSearch};
use yii\widgets\DetailView;

/**
 * @var $this  yii\web\View
 * @var $model common\models\ExportList
 */

$this->title = $model->id;
$this->params['breadcrumbs'][] = [
    'label' => Yii::t('app', 'Export Lists'),
    'url' => UserUrl::setFilters(ExportListSearch::class)
];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="export-films-list-view">

    <h1><?= RbacHtml::encode($this->title) ?></h1>

    <p>
        <?= RbacHtml::a(
            Yii::t('app', 'Delete'),
            ['delete', 'id' => $model->id],
            [
                'class' => 'btn btn-danger',
                'data' => [
                    'confirm' => Yii::t('app', 'Are you sure you want to delete this item?'),
                    'method' => 'post'
                ]
            ]
        ) ?>
    </p>

    <?= DetailView::class::widget([
        'model' => $model,
        'attributes' => [
            Column::widget(),
            Column::widget(['attr' => 'filename']),
            Column::widget(
                ['attr' => 'downloadLink', 'format' => 'url'],
                [
                    'value' => static fn (ExportList $model) => RbacHtml::a(
                        $model->downloadLabel,
                        $model->downloadLink,
                        ['class' => 'badge badge-success']
                    )
                ]
            ),
            Column::widget(['attr' => 'date', 'format' => 'datetime']),
            Column::widget(['attr' => 'count'])
        ]
    ]) ?>

</div>
