<?php

use admin\components\widgets\detailView\Column;
use common\modules\log\{enums\LogOperation, enums\LogStatus, Log as LogModule, widgets\ListColumn};
use yii\bootstrap5\Html;
use yii\widgets\DetailView;

/**
 * @var $this  yii\web\View
 * @var $model common\modules\log\models\Log
 */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => Yii::t(LogModule::MODULE_MESSAGES, 'Logs'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="log-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            Column::widget(),
            Column::widget(['attr' => 'table_model']),
            Column::widget(['attr' => 'record_id']),
            Column::widget(['attr' => 'operation_type', 'items' => LogOperation::class]),
            ListColumn::widget(['attr' => 'field']),
            ListColumn::widget(['attr' => 'before']),
            ListColumn::widget(['attr' => 'after']),
            Column::widget(['attr' => 'time', 'format' => 'datetime']),
            Column::widget(['attr' => 'user_admin_id', 'viewAttr' => 'userAdmin.username']),
            Column::widget(['attr' => 'user_agent']),
            Column::widget(['attr' => 'ip']),
            Column::widget(['attr' => 'status', 'items' => LogStatus::class]),
            Column::widget(['attr' => 'description'])
        ]
    ]) ?>

</div>
