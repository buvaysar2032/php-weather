<?php

use admin\modules\rbac\Module;
use admin\modules\rbac\RbacAsset;
use yii\helpers\{Html, Json};

RbacAsset::register($this);

/**
 * @var $this          yii\web\View
 * @var $model         admin\modules\rbac\models\AssignmentModel
 * @var $usernameField string
 */

$userName = $model->user->{$usernameField};
$this->title = Yii::t(Module::MODULE_MESSAGES, 'Assignment : {0}', $userName);
$this->params['breadcrumbs'][] = ['label' => Yii::t(Module::MODULE_MESSAGES, 'Assignments'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $userName;
$this->render('/layouts/_sidebar');
?>
<div class="assignment-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('../_dualListBox', [
        'opts' => Json::htmlEncode([
            'items' => $model->getItems(),
        ]),
        'assignUrl' => ['assign', 'id' => $model->userId],
        'removeUrl' => ['remove', 'id' => $model->userId]
    ]) ?>

</div>
