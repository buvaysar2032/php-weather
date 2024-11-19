<?php

use yii\bootstrap5\Html;
use yii\data\ArrayDataProvider;
use yii\grid\GridView;
use yii\i18n\Formatter;

/**
 * @var $this yii\web\View
 * @var $data array
 */

$this->title = Yii::t('app', 'Statistics');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="text-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <?php $gridViewDataProvider = new ArrayDataProvider([
        'allModels' => $data,
        'sort' => ['attributes' => ['name', 'value']],
        'pagination' => false
    ]) ?>

    <?= GridView::widget([
        'dataProvider' => $gridViewDataProvider,
        'formatter' => ['class' => Formatter::class, 'nullDisplay' => ''], //Скрыть текст (не задано)
        'columns' => [
            [
                'attribute' => 'name',
                'label' => Yii::t('app', 'Name'),
                'format' => 'raw'
            ],
            [
                'attribute' => 'value',
                'label' => Yii::t('app', 'Value'),
                'format' => 'raw'
            ]
        ]
    ]) ?>
    <h3>Кол-во зарегистрированных пользователей</h3>
    <statistic-block type="line" block-id="users-registered" <?= Yii::$app->themeManager->isDark ? 'is-dark' : null ?>></statistic-block>
</div>
