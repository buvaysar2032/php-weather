<?php

use admin\components\widgets\detailView\Column;
use admin\modules\rbac\components\RbacHtml;
use common\components\helpers\UserUrl;
use common\enums\Boolean;
use common\modules\user\{enums\Status, models\User, models\UserSearch, Module};
use yii\widgets\DetailView;

/**
 * @var $this  yii\web\View
 * @var $model common\modules\user\models\User
 */

$username = $model->username ?: $model->email->value;
$this->title = "#$model->id. $username";
$this->params['breadcrumbs'][] = [
    'label' => Yii::t('app', 'Users'),
    'url' => UserUrl::setFilters(UserSearch::class)
];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="user-view">

    <h1><?= RbacHtml::encode($this->title) ?></h1>

    <p>
        <?php
        echo RbacHtml::a(Yii::t('app', 'Delete'), ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => Yii::t('app', 'Are you sure you want to delete this item?'),
                'method' => 'post'
            ]
        ]);
        if ($model->email && !$model->email->is_confirmed) {
            echo RbacHtml::a(
                Yii::t(Module::MODULE_MESSAGES, 'Send confirmation email'),
                ['user/mail', 'id' => $model->id],
                ['class' => 'btn btn-info']
            );
        } ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            Column::widget(),
            Column::widget(['attr' => 'username']),
            Column::widget(['attr' => 'auth_source']),
            Column::widget(['attr' => 'status', 'items' => Status::class]),
            Column::widget(['attr' => 'userExt.first_name']),
            Column::widget(['attr' => 'userExt.middle_name']),
            Column::widget(['attr' => 'userExt.last_name']),
            Column::widget(['attr' => 'userExt.phone', 'format' => 'phone']),
            Column::widget(['attr' => 'userExt.rules_accepted', 'items' => Boolean::class]),
            [
                'attribute' => 'email.value',
                'value' => static fn(User $model) => RbacHtml::tag(
                    'span',
                    $model->email?->value,
                    [
                        'style' => $model->email?->is_confirmed ? 'color:green' : 'color:red',
                        'title' => $model->email?->is_confirmed
                            ? Yii::t('app', 'Email is confirmed')
                            : Yii::t('app', 'Email is not confirmed')
                    ]
                ),
                'format' => 'raw'
            ],
            Column::widget(['attr' => 'email.is_confirmed', 'items' => Boolean::class]),
            Column::widget(['attr' => 'last_login_at', 'format' => 'datetime']),
            Column::widget(['attr' => 'created_at', 'format' => 'datetime']),
            Column::widget(['attr' => 'updated_at', 'format' => 'datetime']),
            Column::widget(['attr' => 'last_ip', 'format' => 'ip'])
        ]
    ]) ?>

</div>
