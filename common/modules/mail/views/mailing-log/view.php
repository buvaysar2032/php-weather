<?php

use admin\components\widgets\detailView\Column;
use admin\modules\rbac\components\RbacHtml;
use common\components\helpers\UserUrl;
use common\enums\AppType;
use common\modules\mail\{enums\LogStatus, Mail, models\MailTemplateSearch};
use yii\widgets\DetailView;

/**
 * @var $this        yii\web\View
 * @var $model       common\modules\mail\models\MailingLog
 * @var $breadcrumbs bool
 */

$this->title = $model->id;
if ($breadcrumbs) {
    $this->params['breadcrumbs'][] = [
        'label' => Yii::t(Mail::MODULE_MESSAGES, 'Mailing Logs'),
        'url' => UserUrl::setFilters(MailTemplateSearch::class)
    ];
    $this->params['breadcrumbs'][] = $this->title;
}
?>
<div class="mailing-log-view">

    <h1><?= RbacHtml::encode($this->title) ?></h1>

    <p>
        <?php
        if (
            $model->status === LogStatus::Error->value &&
            $model->template !== null
        ) {
            echo RbacHtml::a(
                Yii::t(Mail::MODULE_MESSAGES, 'Repeat'),
                ['repeat', 'id' => $model->id],
                ['class' => 'btn btn-primary']
            );
        }
        echo RbacHtml::a(
            Yii::t('app', 'Delete'),
            ['delete', 'id' => $model->id],
            [
                'class' => 'btn btn-danger',
                'data' => [
                    'confirm' => Yii::t('app', 'Are you sure you want to delete this item?'),
                    'method' => 'post'
                ]
            ]
        ); ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            Column::widget(),
            Column::widget(['attr' => 'app_type', 'items' => AppType::class]),
            Column::widget(['attr' => 'template']),
            Column::widget(['attr' => 'mailing_subject']),
            Column::widget(['attr' => 'mail_to']),
            Column::widget(['attr' => 'user_id', 'viewAttr' => 'user__username', 'pathLink' => 'user/user']),
            Column::widget(['attr' => 'date', 'format' => 'datetime']),
            Column::widget(['attr' => 'status', 'items' => LogStatus::class]),
            Column::widget(['attr' => 'description', 'format' => 'raw'])
        ]
    ]) ?>
    <?php if ($model->mailing_log_id): ?>
        <b>Предыдущая отправка</b>
        <?= $this->render('view', ['model' => $model->mailingLog, 'breadcrumbs' => false]) ?>
    <?php endif ?>
</div>
