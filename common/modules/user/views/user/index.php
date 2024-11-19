<?php

use admin\components\GroupedActionColumn;
use admin\components\widgets\gridView\{Column, ColumnDate, ColumnSelect2, ColumnSwitch};
use admin\modules\rbac\components\RbacHtml;
use admin\widgets\sortableGridView\SortableGridView;
use common\components\export\ExportMenu;
use common\modules\user\{enums\Status, models\User, Module};
use kartik\grid\SerialColumn;
use kartik\icons\Icon;

/**
 * @var $this         yii\web\View
 * @var $searchModel  common\modules\user\models\UserSearch
 * @var $dataProvider yii\data\ActiveDataProvider
 */

$this->title = Yii::t('app', 'Users');
$this->params['breadcrumbs'][] = $this->title;
$this->params['layout_class'] = 'container-fluid';
?>
<div class="user-index">

    <h1><?= RbacHtml::encode($this->title) ?></h1>

    <div class="row justify-content-between">
        <div class="col-auto mr-auto">
            <?= ExportMenu::widget([
                'id' => 'users-export-menu',
                'dataProvider' => $dataProvider,
                'staticConfig' => User::class,
                'filename' => 'users_' . date('d-m-Y_H-i-s'),
                'batchSize' => 100,
            ]) ?>
        </div>
        <div class="col-auto">
            <p>
                <?php
                if (Yii::$app->getModule('user')->enableSocAuthorization) {
                    echo RbacHtml::a(
                        Yii::t(Module::MODULE_MESSAGES, 'View User Social Networks'),
                        ['social-network/index'],
                        ['class' => 'btn btn-success'],
                    );
                } ?>
            </p>
        </div>
    </div>
    <?= SortableGridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'pjax' => true,
        'columns' => [
            ['class' => SerialColumn::class],

            Column::widget(),
            Column::widget(['attr' => 'username', 'editable' => false]),
            Column::widget(['attr' => 'auth_source', 'editable' => false, 'width' => 170]),
            Column::widget(['attr' => 'userExt.first_name', 'editable' => false]),
            Column::widget(['attr' => 'userExt.last_name', 'editable' => false]),
            Column::widget(['attr' => 'userExt.phone', 'format' => 'phone', 'editable' => false]),
            [
                'attribute' => 'email.value',
                'value' => static fn(User $model)
                    => RbacHtml::tag(
                    'span',
                    $model->email?->value,
                    [
                        'style' => $model->email?->is_confirmed ? 'color:green' : 'color:red',
                        'title' => $model->email?->is_confirmed
                            ? Yii::t('app', 'Email is confirmed')
                            : Yii::t('app', 'Email is not confirmed'),
                        'data-bs-toggle' => 'tooltip',
                    ],
                ),
                'filterInputOptions' => [
                    'class' => 'form-control',
                    'placeholder' => Yii::t('app', 'Search'),
                ],
                'format' => 'raw',
            ],
            ColumnSwitch::widget(['attr' => 'email.is_confirmed', 'editable' => false]),
            ColumnDate::widget(['attr' => 'last_login_at', 'searchModel' => $searchModel, 'editable' => false]),
            ColumnSelect2::widget([
                'attr' => 'status',
                'items' => Status::class,
                'hideSearch' => true,
                'width' => 120,
            ]),

            [
                'class' => GroupedActionColumn::class,
                'template' => '{view} {mail} {delete}',
                'buttons' => [
                    'mail' => static function ($url, User $model) {
                        if ($model->email && !$model->email->is_confirmed) {
                            return RbacHtml::a(Icon::show('envelope'), $url, [
                                'class' => 'text-warning',
                                'title' => Yii::t(Module::MODULE_MESSAGES, 'Send confirmation email'),
                                'data-bs-toggle' => 'tooltip',
                            ]);
                        }
                        return null;
                    },
                ],
            ],
        ],
    ]) ?>
</div>
