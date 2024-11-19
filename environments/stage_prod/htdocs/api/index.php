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
require $root_path . '/api/config/bootstrap.php';

$config = yii\helpers\ArrayHelper::merge(
    require $root_path . '/common/config/main.php',
    require $root_path . '/common/config/main-local.php',
    require $root_path . '/api/config/main.php',
    require $root_path . '/api/config/main-local.php'
);

error_reporting(E_ALL & ~E_NOTICE);
(new yii\web\Application($config))->run();
