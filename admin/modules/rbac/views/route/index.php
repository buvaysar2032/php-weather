<?php

use admin\modules\rbac\{components\RbacHtml, Module, RbacRouteAsset};
use yii\helpers\Json;

RbacRouteAsset::register($this);

/**
 * @var $this   yii\web\View
 * @var $routes array
 */

$this->title = Yii::t(Module::MODULE_MESSAGES, 'Routes');
$this->params['breadcrumbs'][] = $this->title;
$this->render('/layouts/_sidebar');
?>
<h1><?= RbacHtml::encode($this->title) ?></h1>
<?= RbacHtml::a(Yii::t(Module::MODULE_MESSAGES, 'Refresh'), ['refresh'], [
    'class' => 'btn btn-primary',
    'id' => 'btn-refresh'
]) ?>
<?= $this->render('../_dualListBox', [
    'opts' => Json::htmlEncode(['items' => $routes]),
    'assignUrl' => ['assign'],
    'removeUrl' => ['remove']
]) ?>
