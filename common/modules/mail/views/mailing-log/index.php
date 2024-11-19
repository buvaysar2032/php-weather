<?php

use admin\components\GroupedActionColumn;
use admin\components\widgets\{gridView\Column, gridView\ColumnDate, gridView\ColumnSelect2};
use admin\modules\rbac\components\RbacHtml;
use admin\widgets\sortableGridView\SortableGridView;
use common\enums\AppType;
use common\modules\mail\{enums\LogStatus, Mail, models\MailingLog};
use kartik\grid\SerialColumn;
use kartik\icons\Icon;
use yii\bootstrap5\Html;
use yii\helpers\Url;

/**
 * @var $this         yii\web\View
 * @var $searchModel  common\modules\mail\models\MailingLogSearch
 * @var $dataProvider yii\data\ActiveDataProvider
 */

$this->title = Yii::t(Mail::MODULE_MESSAGES, 'Mailing Logs');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="mailing-log-index">

    <h1><?= RbacHtml::encode($this->title) ?></h1>

    <div class="row justify-content-between">
        <div class="col-auto mr-auto">
            <?= RbacHtml::a(
                Yii::t(Mail::MODULE_MESSAGES, 'Repeat All Errors'),
                ['repeat-all'],
                ['class' => 'btn btn-info']
            ) ?>
            <?= RbacHtml::a(
                Yii::t(Mail::MODULE_MESSAGES, 'Errors List'),
                ['errors'],
                ['class' => 'btn btn-warning']
            ) ?>
        </div>
        <div class="col-auto">
            <?= Html::a(
                Yii::t(Mail::MODULE_MESSAGES, 'Errors FAQ'),
                ['faq'],
                ['class' => 'btn btn-info']
            ) ?>
        </div>
    </div>

    <p></p>

    <?= SortableGridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'pjax' => true,
        'columns' => [
            ['class' => SerialColumn::class],

            Column::widget(),
            ColumnSelect2::widget([
                'attr' => 'app_type',
                'items' => AppType::class,
                'hideSearch' => true,
                'editable' => false
            ]),
            Column::widget(['attr' => 'template', 'editable' => false, 'width' => 120]),
            ColumnSelect2::widget(['attr' => 'mailing_subject', 'editable' => false]),
            Column::widget(['attr' => 'mail_to']),
            ColumnSelect2::widget([
                'attr' => 'user_id',
                'viewAttr' => 'user.username',
                'pathLink' => 'user/user',
                'editable' => false,
                'placeholder' => Yii::t('app', 'Search...'),
                'ajaxSearchConfig' => [
                    'url' => Url::to(['/user/user/list']),
                    'searchModel' => $searchModel
                ]
            ]),
            ColumnDate::widget(['attr' => 'date', 'searchModel' => $searchModel, 'editable' => false]),
            ColumnSelect2::widget([
                'attr' => 'status',
                'items' => LogStatus::class,
                'editable' => false,
                'hideSearch' => true,
                'width' => 100
            ]),

            [
                'class' => GroupedActionColumn::class,
                'template' => '{view} {repeat} {delete}',
                'buttons' => [
                    'repeat' => static function (string $url, MailingLog $model) {
                        if ($model->status === LogStatus::Error->value && $model->mailing_id) {
                            return RbacHtml::a(Icon::show('redo-alt'), $url, [
                                'title' => Yii::t(Mail::MODULE_MESSAGES, 'Repeat'),
                                'data-pjax' => '0',
                                'data-bs-toggle' => 'tooltip'
                            ]);
                        }
                        return null;
                    }
                ]
            ]
        ]
    ]) ?>

</div>
