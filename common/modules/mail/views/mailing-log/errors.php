<?php

use admin\components\widgets\gridView\{Column, ColumnDate, ColumnSelect2};
use admin\modules\rbac\components\RbacHtml;
use admin\widgets\sortableGridView\SortableGridView;
use common\components\helpers\UserUrl;
use common\enums\AppType;
use common\modules\mail\{enums\LogStatus,
    Mail,
    models\Mailing,
    models\MailingLog,
    models\MailingLogSearch,
    models\MailTemplateSearch};
use kartik\grid\{ActionColumn, SerialColumn};
use kartik\icons\Icon;
use yii\helpers\Url;

/**
 * @var $this         yii\web\View
 * @var $searchModel  common\modules\mail\models\MailingLogSearch
 * @var $dataProvider yii\data\ActiveDataProvider
 */

$this->title = Yii::t(Mail::MODULE_MESSAGES, 'Error Mailing Logs');
$this->params['breadcrumbs'][] = [
    'label' => Yii::t(Mail::MODULE_MESSAGES, 'Mailing Logs'),
    'url' => UserUrl::setFilters(MailingLogSearch::class)
];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="mailing-log-index">

    <h1><?= RbacHtml::encode($this->title) ?></h1>

    <p>
        <?= RbacHtml::a(
            Yii::t(Mail::MODULE_MESSAGES, 'Repeat All Errors'),
            ['repeat-all'],
            ['class' => 'btn btn-info']
        ) ?>
        <?= RbacHtml::a(Yii::t(Mail::MODULE_MESSAGES, 'Errors FAQ'), ['faq'], ['class' => 'btn btn-info']) ?>
    </p>

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
                'editable' => false,
                'hideSearch' => true
            ]),
            Column::widget([
                'attr' => 'mailing_subject',
                'editable' => false
            ]),
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
            Column::widget(
                ['attr' => 'description', 'editable' => false],
                ['contentOptions' => ['style' => 'white-space: pre-wrap;']]
            ),

            [
                'class' => ActionColumn::class,
                'template' => '{view} {repeat} {delete}',
                'buttons' => [
                    'repeat' => static function (string $url, MailingLog $model) {
                        if ($model->status === LogStatus::Error->value && $model->mailing_id) {
                            $customUrl = Yii::$app->urlManager
                                ->createUrl(['mail/mailing-log/repeat', 'id' => $model['id']]);
                            return RbacHtml::a(Icon::show('repeat'), $customUrl, [
                                'title' => Yii::t('app', 'Repeat'),
                                'data-pjax' => '0'
                            ]);
                        }
                        return null;
                    }
                ]
            ]
        ]
    ]) ?>
</div>
