<?php

use Dotenv\Dotenv;

$public = '/htdocs';
$root = '@root';
Yii::setAlias('@common', dirname(__DIR__));
Yii::setAlias($root, dirname(__DIR__, 2));
Yii::setAlias('@api', $root . '/api');
Yii::setAlias('@admin', $root . '/admin');
Yii::setAlias('@frontend', $root . '/frontend');
Yii::setAlias('@console', $root . '/console');
Yii::setAlias('@vue', $root . '/vue');
Yii::setAlias('@public', $root . $public);
Yii::setAlias('@htdocs', $root . $public);
Yii::setAlias('@uploads', $root . $public . common\components\helpers\UserUrl::UPLOADS);
if (file_exists(dirname(__DIR__, 2) . '/.env')) {
    $dotenv = Dotenv::createImmutable(dirname(__DIR__, 2));
    $dotenv->load();
}
unset($public, $root, $dotenv);
