<?php

use admin\components\GroupedActionColumn;
use common\components\export\ExportMenu as AppExportMenu;
use admin\components\widgets\{gridView\Column, gridView\ColumnDate, gridView\ColumnSelect2};
use admin\models\UserAdmin;
use admin\widgets\sortableGridView\SortableGridView;
use common\modules\log\{enums\LogOperation, enums\LogStatus, Log as LogModule, models\Log, widgets\ListColumn};
use kartik\export\ExportMenu;
use kartik\grid\SerialColumn;
use yii\bootstrap5\Html;

/**
 * @var $this         yii\web\View
 * @var $searchModel  common\modules\log\models\LogSearch
 * @var $dataProvider yii\data\ActiveDataProvider
 */

$this->title = Yii::t(LogModule::MODULE_MESSAGES, 'Logs');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="log-index">
    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?php
        $gridColumns = [
            ['class' => SerialColumn::class],
            'table_model',
            'record_id',
            'operation_type',
            'field',
            'before',
            'after',
            'time',
            'user_admin_id',
            'user_agent',
            'ip',
            'status',
            'description'
        ];
        // Renders an export dropdown menu
        echo AppExportMenu::widget([
            'id' => 'log-export-menu',
            'dataProvider' => $dataProvider,
            'columns' => $gridColumns,
            'tableName' => $searchModel::tableName(),
            'filename' => 'export_logs_' . date('j.m.Y_G:i'),
            'exportConfig' => [
                ExportMenu::FORMAT_HTML => false,
                ExportMenu::FORMAT_EXCEL => false,
                ExportMenu::FORMAT_EXCEL_X => false,
                ExportMenu::FORMAT_PDF => false
            ],
            'batchSize' => 100
        ]) ?>
    </p>

    <?= SortableGridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'pjax' => true,
        'columns' => [
            ['class' => SerialColumn::class],

            ColumnSelect2::widget([
                'attr' => 'table_model',
                'items' => Log::getTableModels(),
                'editable' => false
            ]),
            ColumnSelect2::widget([
                'attr' => 'operation_type',
                'items' => LogOperation::class,
                'hideSearch' => true,
                'editable' => false
            ]),
            Column::widget(['attr' => 'record_id', 'editable' => false, 'width' => 20]),
            ListColumn::widget(['attr' => 'field']),
            ListColumn::widget(['attr' => 'before', 'width' => 250]),
            ListColumn::widget(['attr' => 'after', 'width' => 250]),
            ColumnDate::widget(['attr' => 'time', 'searchModel' => $searchModel, 'editable' => false]),
            ColumnSelect2::widget([
                'attr' => 'user_admin_id',
                'items' => UserAdmin::find()->select(['username AS name', 'id'])->indexBy('id')->column(),
                'editable' => false
            ]),
            Column::widget(['attr' => 'ip', 'editable' => false]),
            ColumnSelect2::widget(['attr' => 'status', 'items' => LogStatus::class, 'editable' => false]),

            ['class' => GroupedActionColumn::class, 'template' => '{view}']
        ]
    ]) ?>

</div>
