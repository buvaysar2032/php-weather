<?php

use admin\models\UserAdminSearch;
use common\components\helpers\UserUrl;
use yii\bootstrap5\Html;

/**
 * @var $this  yii\web\View
 * @var $model admin\models\UserAdmin
 */

$this->title = Yii::t('app', 'Update User Admin: {name}', ['name' => $model->username]);
$this->params['breadcrumbs'][] = [
    'label' => Yii::t('app', 'User Admins'),
    'url' => UserUrl::setFilters(UserAdminSearch::class)
];
$this->params['breadcrumbs'][] = ['label' => $model->username, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('app', 'Update');
?>
<div class="user-admin-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', ['model' => $model]) ?>

</div>
