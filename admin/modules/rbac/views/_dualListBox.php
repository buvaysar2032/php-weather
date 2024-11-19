<?php

use admin\modules\rbac\components\RbacHtml;
use admin\modules\rbac\Module;
use yii\web\View;

/**
 * @var $this      yii\web\View
 * @var $assignUrl array
 * @var $removeUrl array
 * @var $opts      string
 */

$this->registerJs("const _opts = $opts;", View::POS_BEGIN);
?>
<div class="row">
    <div class="col-lg-5">
        <input class="form-control search" data-target="available"
               placeholder="<?= Yii::t(Module::MODULE_MESSAGES, 'Search for available') ?>">
        <br/>
        <select multiple size="20" class="form-control list" data-target="available"></select>
    </div>
    <div class="col-lg-2">
        <div class="move-buttons">
            <br><br>
            <?= RbacHtml::a('&gt;&gt;', $assignUrl, [
                'class' => 'btn btn-success btn-assign',
                'data-target' => 'available',
                'title' => Yii::t(Module::MODULE_MESSAGES, 'Assign'),
            ]) ?>
            <br/><br/>
            <?= RbacHtml::a('&lt;&lt;', $removeUrl, [
                'class' => 'btn btn-danger btn-assign',
                'data-target' => 'assigned',
                'title' => Yii::t(Module::MODULE_MESSAGES, 'Remove'),
            ]) ?>
        </div>
    </div>
    <div class="col-lg-5">
        <input class="form-control search" data-target="assigned"
               placeholder="<?= Yii::t(Module::MODULE_MESSAGES, 'Search for assigned') ?>">
        <br/>
        <select multiple size="20" class="form-control list" data-target="assigned"></select>
    </div>
</div>