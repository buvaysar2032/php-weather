<?php

use admin\components\widgets\detailView\Column;
use admin\enums\AdminStatus;
use admin\models\UserAdminSearch;
use admin\modules\rbac\components\RbacHtml;
use common\components\helpers\UserUrl;
use yii\widgets\DetailView;

/**
 * @var $this  yii\web\View
 * @var $model admin\models\UserAdmin
 */

$this->title = $model->username;
$this->params['breadcrumbs'][] = [
    'label' => Yii::t('app', 'User Admins'),
    'url' => UserUrl::setFilters(UserAdminSearch::class)
];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="user-admin-view">

    <h1><?= RbacHtml::encode($this->title) ?></h1>

    <p>
        <?= RbacHtml::a(Yii::t('app', 'Update'), ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= RbacHtml::a(
            Yii::t('app', 'Delete'),
            ['delete', 'id' => $model->id],
            [
                'class' => 'btn btn-danger',
                'data' => [
                    'confirm' => Yii::t('app', 'Are you sure you want to delete this item?'),
                    'method' => 'post'
                ]
            ]
        ) ?>
        <?= RbacHtml::a(
            Yii::t('app', 'Password Change'),
            ['password-change', 'id' => $model->id],
            ['class' => 'btn btn-primary']
        ) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            Column::widget(),
            Column::widget(['attr' => 'username']),
            Column::widget(['attr' => 'email', 'format' => 'email']),
            Column::widget(['attr' => 'status', 'items' => AdminStatus::class]),
            Column::widget(['attr' => 'created_at', 'format' => 'datetime']),
            Column::widget(['attr' => 'updated_at', 'format' => 'datetime'])
        ]
    ]) ?>

</div>
