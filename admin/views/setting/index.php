<?php

use admin\components\widgets\gridView\Column;
use admin\widgets\sortableGridView\SortableGridView;
use common\models\Setting;
use kartik\grid\SerialColumn;
use yii\bootstrap5\Html;

/**
 * @var $this         yii\web\View
 * @var $dataProvider yii\data\ActiveDataProvider
 */

$this->title = Yii::t('app', 'Settings');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="settings-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= SortableGridView::widget([
        'dataProvider' => $dataProvider,
        'pjax' => true,
        'columns' => [
            ['class' => SerialColumn::class],

            Column::widget(['attr' => 'parameter', 'editable' => false]),
            Column::widget([
                'attr' => 'value',
                'viewAttr' => 'columnValue',
                'width' => 500,
                'type' => static fn(Setting $setting) => $setting->inputWidget,
                'format' => 'raw'
            ]),
            Column::widget(['attr' => 'description'])
        ]
    ]) ?>
</div>
