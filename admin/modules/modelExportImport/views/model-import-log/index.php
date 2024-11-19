<?php

use admin\components\GroupedActionColumn;
use admin\widgets\sortableGridView\SortableGridView;
use admin\components\widgets\gridView\{Column, ColumnDate};
use admin\modules\modelExportImport\ModelExportImport;
use kartik\grid\SerialColumn;
use kartik\icons\Icon;
use yii\bootstrap5\Html;

/**
 * @var $this         yii\web\View
 * @var $searchModel  admin\modules\modelExportImport\models\ModelImportLogSearch
 * @var $dataProvider yii\data\ActiveDataProvider
 */

$this->title = Yii::t(ModelExportImport::MODULE_MESSAGES, 'Model Import Logs');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="model-import-log-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= SortableGridView::widget([
        'dataProvider' => $dataProvider,
        'pjax' => true,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => SerialColumn::class],

            Column::widget(),
            ColumnDate::widget(['attr' => 'imported_at', 'searchModel' => $searchModel, 'editable' => false]),

            [
                'class' => GroupedActionColumn::class,
                'template' => '{view} {reverse} {delete}',
                'buttons' => [
                    'reverse' => static fn (string $url) => Html::a(
                        Icon::show('redo'),
                        $url,
                        ['data' => ['pjax' => 0, 'bs-toggle' => 'tooltip']]
                    )
                ]
            ]
        ]
    ]) ?>
</div>