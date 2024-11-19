<?php

use admin\components\widgets\detailView\Column;
use admin\modules\modelExportImport\{ModelExportImport, models\ModelImportLog, models\ModelImportLogSearch};
use common\components\helpers\UserUrl;
use yii\bootstrap5\Html;
use yii\helpers\Json;
use yii\widgets\DetailView;

/**
 * @var $this  yii\web\View
 * @var $model admin\modules\modelExportImport\models\ModelImportLog
 */

$this->title = $model->id;
$this->params['breadcrumbs'][] = [
    'label' => Yii::t(ModelExportImport::MODULE_MESSAGES, 'Model Import Logs'),
    'url' => UserUrl::setFilters(ModelImportLogSearch::class)
];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="model-import-log-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?php
        echo Html::a(
            Yii::t(ModelExportImport::MODULE_MESSAGES, 'Reverse Model'),
            ['reverse', 'id' => $model->id],
            ['class' => 'btn btn-warning']
        );
        echo Html::a(
            Yii::t('app', 'Delete'),
            ['delete', 'id' => $model->id],
            [
                'class' => 'btn btn-danger',
                'data' => [
                    'confirm' => Yii::t('app', 'Are you sure you want to delete this item?'),
                    'method' => 'post',
                ]
            ]
        ); ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            Column::widget(),
            Column::widget(['attr' => 'model_class']),
            Column::widget(['attr' => 'unique_field']),
            Column::widget(['attr' => 'unique_field_value']),
            Column::widget(
                ['attr' => 'dump_before', 'format' => 'raw'],
                [
                    'value' => static fn (ModelImportLog $model) => '<pre>' .
                        Json::encode(
                            Json::decode($model->dump_after),
                            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
                        ) .
                        '</pre>'
                ]
            ),
            Column::widget(
                ['attr' => 'dump_after', 'format' => 'raw'],
                [
                    'value' => static fn (ModelImportLog $model) => '<pre>' .
                        Json::encode(
                            Json::decode($model->dump_after),
                            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
                        ) .
                        '</pre>'
                ]
            ),
            Column::widget(['attr' => 'imported_at', 'format' => 'datetime'])
        ]
    ]) ?>

</div>