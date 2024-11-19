<?php

use admin\components\auth\RbacHelper;
use admin\components\GroupedActionColumn;
use admin\components\widgets\{gridView\Column, gridView\ColumnDate, gridView\ColumnSelect2};
use admin\enums\AdminStatus;
use admin\widgets\sortableGridView\SortableGridView;
use kartik\grid\SerialColumn;
use kartik\icons\Icon;
use yii\bootstrap5\Html;

/**
 * @var $this         yii\web\View
 * @var $searchModel  admin\models\UserAdminSearch
 * @var $dataProvider yii\data\ActiveDataProvider
 * @var $model        admin\models\AdminSignupForm
 */

$this->title = Yii::t('app', 'User Admins');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="user-admin-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= $this->render('_create_modal', ['model' => $model]) ?>
    </p>

    <?= SortableGridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'pjax' => true,
        'columns' => [
            ['class' => SerialColumn::class],

            Column::widget(),
            Column::widget(['attr' => 'username', 'editable' => false]),
            Column::widget(['attr' => 'email', 'format' => 'email', 'editable' => false]),
            ColumnSelect2::widget(['attr' => 'status', 'items' => AdminStatus::class, 'hideSearch' => true]),
            ColumnDate::widget(['attr' => 'created_at', 'searchModel' => $searchModel, 'editable' => false]),
            ColumnDate::widget(['attr' => 'updated_at', 'searchModel' => $searchModel, 'editable' => false]),
            [
                'class' => GroupedActionColumn::class,
                'template' => '{view} {update} {password-change} {delete}',
                'buttons' => [
                    'password-change' => static fn ($url) => Html::a(
                        Icon::show('ellipsis-h'),
                        $url,
                        ['title' => 'Смена пароля', 'data-pjax' => '0']
                    )
                ]
            ]
        ]
    ]) ?>
</div>