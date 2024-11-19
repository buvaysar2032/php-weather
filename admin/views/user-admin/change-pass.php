<?php

use admin\models\UserAdminSearch;
use common\components\{helpers\UserUrl};
use common\widgets\AppActiveForm;
use yii\bootstrap5\Html;

/**
 * @var $this  yii\web\View
 * @var $model admin\models\PasswordChangeForm
 */

$this->title = Yii::t('app', 'Password Change');
$this->params['breadcrumbs'][] = [
    'label' => Yii::t('app', 'User Admins'),
    'url' => UserUrl::setFilters(UserAdminSearch::class)
];
$this->params['breadcrumbs'][] = ['label' => $model->username, 'url' => ['view', 'id' => $model->userAdminId]];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="user-profile-password-change">

    <h1><?= Html::encode($this->title) ?></h1>

    <div class="user-form">
        <div class="row">
            <div class="col-lg-5">
                <?php $form = AppActiveForm::begin() ?>

                <?= $form->field($model, 'currentPassword')
                    ->passwordInput(['maxlength' => true, 'autocomplete' => 'current-password']) ?>

                <?= $form->field($model, 'newPassword')
                    ->passwordInput(['maxlength' => true, 'autocomplete' => 'new-password']) ?>

                <?= $form->field($model, 'newPasswordRepeat')
                    ->passwordInput(['maxlength' => true, 'autocomplete' => 'new-password']) ?>

                <div class="form-group">
                    <?= Html::submitButton(Yii::t('app', 'Save'), ['class' => 'btn btn-primary']) ?>
                </div>

                <?php AppActiveForm::end() ?>
            </div>
        </div>
    </div>
</div>
