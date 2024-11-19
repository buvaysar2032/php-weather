<?php

use admin\widgets\sortableGridView\SortableGridView;
use admin\components\widgets\{gridView\Column, gridView\ColumnDate, gridView\ColumnSelect2};
use common\modules\user\{helpers\UserHelper, Module};
use kartik\grid\SerialColumn;
use yii\bootstrap5\Html;
use yii\helpers\Url;

/**
 * @var $this         yii\web\View
 * @var $searchModel  common\modules\user\models\SocialNetworkSearch
 * @var $dataProvider yii\data\ActiveDataProvider
 */

$this->title = Yii::t(Module::MODULE_MESSAGES, 'Social Networks');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Users'), 'url' => ['user/index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="social-network-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= SortableGridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'pjax' => true,
        'columns' => [
            ['class' => SerialColumn::class],

            Column::widget(),
            ColumnSelect2::widget([
                'attr' => 'user_id',
                'editable' => false,
                'viewAttr' => 'user__username',
                'pathLink' => 'user',
                'placeholder' => Yii::t('app', 'Search...'),
                'ajaxSearchConfig' => [
                    'url' => Url::to(['/user/user/list']),
                    'searchModel' => $searchModel
                ]
            ]),
            ColumnSelect2::widget([
                'attr' => 'social_network_id',
                'items' => UserHelper::getSocialList(),
                'editable' => false,
                'hideSearch' => true
            ]),
            Column::widget(['attr' => 'user_auth_id', 'editable' => false]),
            ColumnDate::widget(['attr' => 'last_auth_date', 'searchModel' => $searchModel, 'editable' => false])
        ]
    ]) ?>
</div>
