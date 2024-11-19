<?php

use admin\components\GroupedActionColumn;
use admin\components\widgets\gridView\Column;
use admin\modules\rbac\components\RbacHtml;
use admin\widgets\sortableGridView\SortableGridView;
use admin\widgets\tooltip\TooltipWidget;
use common\components\helpers\UserUrl;
use common\modules\mail\Mail;
use common\modules\mail\models\Template;
use kartik\grid\SerialColumn;

/**
 * @var $this         yii\web\View
 * @var $dataProvider yii\data\ArrayDataProvider
 */

$this->title = Yii::t(Mail::MODULE_MESSAGES, 'Templates');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="mail-template-index">

    <h1><?= RbacHtml::encode($this->title) ?></h1>

    <p>
        <?= RbacHtml::a(Yii::t(Mail::MODULE_MESSAGES, 'Create Template'), ['create'], ['class' => 'btn btn-success']) ?>
        <?= RbacHtml::a(
            Yii::t(Mail::MODULE_MESSAGES, 'Test Mailing') . ' ' .
            TooltipWidget::widget(['title' => Yii::t(Mail::MODULE_MESSAGES, 'Form for sending test emails')]),
            ['test'],
            ['class' => 'btn btn-info']
        ) ?>
    </p>

    <?= SortableGridView::widget([
        'dataProvider' => $dataProvider,
        'pjax' => true,
        'columns' => [
            ['class' => SerialColumn::class],

            Column::widget(['attr' => 'name', 'editable' => false]),

            [
                'class' => GroupedActionColumn::class,
                'urlCreator' => static fn(string $action, Template $model) => UserUrl::toRoute([
                    $action,
                    'name' => $model->name
                ])
            ]
        ]
    ]) ?>

</div>
