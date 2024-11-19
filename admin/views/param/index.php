<?php

use admin\components\GroupedActionColumn;
use admin\components\widgets\gridView\{Column, ColumnSwitch};
use admin\modules\rbac\components\RbacHtml;
use admin\widgets\sortableGridView\SortableGridView;
use common\models\Param;
use kartik\icons\Icon;
use yii\bootstrap5\Html;

/**
 * @var $this         yii\web\View
 * @var $dataProvider yii\data\ActiveDataProvider
 * @var $searchModel  common\models\ParamSearch;
 * @var $model        Param
 */

$this->title = Yii::t('app', 'Params');
$this->params['breadcrumbs'][] = $this->title;
$readonly = static fn(Param $model) => !$model->deletable
?>
<div class="container">
    <div class="params-index">

        <h1><?= Html::encode($this->title) ?></h1>

        <div>
            <?= $this->render('_create_modal', ['model' => $model]); ?>
        </div>

        <?= SortableGridView::widget([
            'dataProvider' => $dataProvider,
            'pjax' => true,
            'filterModel' => $searchModel,
            'columns' => [
                Column::widget(['attr' => 'group', 'readonly' => $readonly]),
                Column::widget(['attr' => 'key', 'readonly' => $readonly]),
                Column::widget([
                    'attr' => 'value',
                    'viewAttr' => 'columnValue',
                    'width' => 500,
                    'type' => static fn(Param $param) => $param->inputWidget,
                    'format' => 'raw'
                ]),
                Column::widget(['attr' => 'description']),
                ColumnSwitch::widget(['attr' => 'is_active']),
                [
                    'class' => GroupedActionColumn::class,
                    'template' => '{delete}',
                    'buttons' => [
                        'delete' => static function (string $url, Param $param) {
                            if (!$param->deletable) {
                                return null;
                            }
                            return RbacHtml::a(Icon::show('trash-alt'), $url, [
                                'class' => 'text-danger',
                                'title' => Yii::t('yii', 'Delete'),
                                'aria-label' => Yii::t('yii', 'Delete'),
                                'data-pjax' => '0',
                                'data-method' => 'post',
                                'data-confirm' => Yii::t(
                                    'kvgrid',
                                    'Are you sure to delete this {item}?',
                                    ['item' => Yii::t('kvgrid', 'item')]
                                )
                            ]);
                        }
                    ]
                ]
            ]
        ]) ?>

    </div>
</div>
