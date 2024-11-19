<?php

defined('YII_DEBUG') || define('YII_DEBUG', false);
defined('YII_ENV') || define('YII_ENV', $_ENV['YII_ENV'] ?? getenv('YII_ENV') ?: 'prod');

$root_path = dirname(__DIR__, 2);

if (file_exists($root_path . '/c3.php')) {
    require $root_path . '/c3.php';
}
require $root_path . '/vendor/autoload.php';
require_once $root_path . '/vendor/yiisoft/yii2/Yii.php';
require $root_path . '/common/config/bootstrap.php';
require $root_path . '/admin/config/bootstrap.php';

$config = yii\helpers\ArrayHelper::merge(
    require $root_path . '/common/config/main.php',
    require $root_path . '/common/config/main-local.php',
    require $root_path . '/admin/config/main.php',
    require $root_path . '/admin/config/main-local.php'
);
if (!empty($config['modules']['debug'])) {
    unset($config['modules']['debug']);
}
if (!empty($config['modules']['gii'])) {
    unset($config['modules']['gii']);
}

date_default_timezone_set('Europe/Moscow');
error_reporting(E_ALL & ~E_NOTICE);
return new yii\web\Application($config);

