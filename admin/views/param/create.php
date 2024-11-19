<?php

use common\components\helpers\UserUrl;
use common\models\ParamSearch;
use yii\bootstrap5\Html;

/**
 * @var $this  yii\web\View
 * @var $model common\models\Param
 */

$this->title = Yii::t('app', 'Create Param');
$this->params['breadcrumbs'][] = [
    'label' => Yii::t('app', 'Params'),
    'url' => UserUrl::setFilters(ParamSearch::class)
];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="param-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', ['model' => $model, 'isCreate' => true]) ?>

</div>
