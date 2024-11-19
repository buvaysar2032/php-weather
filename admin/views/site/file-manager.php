<?php

use admin\widgets\ckfinder\CKFinder;
use yii\bootstrap5\Html;

/**
 * @var $this         yii\web\View
 * @var $sessionCache string
 */

$this->title = Yii::t('app', 'File Manager');
$this->params['breadcrumbs'][] = $this->title
?>
<div>
    <h1><?= Html::encode($this->title) ?></h1>
    <p>Ключ:
    <pre><?= Yii::$app->session->get($sessionCache) ?: Yii::$app->cache->get($sessionCache) ?></pre>
    </p>
    <?= Html::a('Регенерировать ключ', ['regenerate-key'], ['class' => 'btn btn-warning']) ?>
    <?= CKFinder::widget() ?>
</div>

