<?php

use admin\modules\rbac\Module;
use yii\helpers\Html;

/**
 * @var $this  yii\web\View
 * @var $model admin\modules\rbac\models\BizRuleModel
 */

$this->title = Yii::t(Module::MODULE_MESSAGES, 'Create Rule');
$this->params['breadcrumbs'][] = ['label' => Yii::t(Module::MODULE_MESSAGES, 'Rules'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
$this->render('/layouts/_sidebar');
?>
<div class="rule-item-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', ['model' => $model]) ?>

</div>