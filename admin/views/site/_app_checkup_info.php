<?php

use common\components\Environment;
use common\components\helpers\UserFileHelper;
use kartik\grid\GridView;
use yii\bootstrap5\Html;
use yii\data\ArrayDataProvider;
use yii\helpers\ArrayHelper;

/**
 * @var $this       yii\web\View
 * @var $db_version string
 */

$root_prefix = Yii::getAlias('@root');
$api_config = ArrayHelper::merge(
    require "$root_prefix/api/config/params.php",
    require "$root_prefix/api/config/params-local.php"
);
$getParam = static function ($param, $target = null) use ($api_config) {
    if ($target === 'api') {
        return ArrayHelper::getValue($api_config, $param);
    }
    return ArrayHelper::getValue(Yii::$app->params, $param);
};
$time = time();
$datetimeFormat = preg_replace('/^php:/', '', Yii::$app->formatter->datetimeFormat);

$check_func = static function (string $param_name, $db_version = null) use (
    $getParam,
    $time,
    $datetimeFormat,
    $root_prefix
) {
    $required = $getParam($param_name);
    $apiRequired = $getParam($param_name, 'api');
    if (!$required) {
        return true;
    }
    switch ($param_name) {
        case 'required-php-version':
            if ($apiRequired) {
                $result = version_compare(PHP_VERSION, $required) !== -1 &&
                    version_compare(PHP_VERSION, $apiRequired) !== -1;
            } else {
                $result = version_compare(PHP_VERSION, $required) !== -1;
            }
            break;
        case 'required-db-version':
            if ($apiRequired) {
                $result = version_compare($db_version, $required) !== -1 &&
                    version_compare($db_version, $apiRequired) !== -1;
            } else {
                $result = version_compare($db_version, $required) !== -1;
            }
            break;
        case 'memory-limit':
            $required = UserFileHelper::stringToBytes($required);
            $current = UserFileHelper::stringToBytes(ini_get('memory_limit'));
            if ($apiRequired) {
                $apiRequired = UserFileHelper::stringToBytes($apiRequired);
                $result = $required <= $current && $apiRequired <= $current;
            } else {
                $result = $required <= $current;
            }
            break;
        case 'disk-free-space':
            $required = UserFileHelper::stringToBytes($required);
            $current = disk_free_space($root_prefix);
            if ($apiRequired) {
                $apiRequired = UserFileHelper::stringToBytes($apiRequired);
                $result = $required <= $current && $apiRequired <= $current;
            } else {
                $result = $required <= $current;
            }
            break;
        case 'upload-max-filesize':
            $required = UserFileHelper::stringToBytes($required);
            $current = UserFileHelper::stringToBytes(ini_get('upload_max_filesize'));
            if ($apiRequired) {
                $apiRequired = UserFileHelper::stringToBytes($apiRequired);
                $result = $required <= $current && $apiRequired <= $current;
            } else {
                $result = $required <= $current;
            }
            break;
        case 'time_format':
            $result = strcmp(Yii::$app->formatter->asDatetime($time), date($datetimeFormat, $time));
            break;
        default:
            $result = false;
            break;
    }
    return $result;
};
$data = [];
$versionFile = "$root_prefix/version.php";
if (file_exists($versionFile)) {
    require $versionFile;
    $data[] = [
        'key' => 'app_version',
        'currentValue' => Yii::$app->formatter->asDatetime(PROJECT_VERSION),
        'requiredValue' => '',
        'apiRequiredValue' => '',
        'description' => 'Дата последнего коммита',
        'check' => true,
        'mandatory' => true,
    ];
}

$data = array_merge($data, [
    [
        'key' => 'phpversion',
        'currentValue' => PHP_VERSION,
        'requiredValue' => $getParam('required-php-version'),
        'apiRequiredValue' => '',
        'description' => '',
        'check' => $check_func('required-php-version'),
        'mandatory' => true
    ],
    [
        'key' => 'php_bit_capacity',
        'currentValue' => PHP_INT_MAX === 2147483647 ? 'x32' : 'x64',
        'requiredValue' => 'x64',
        'apiRequiredValue' => '',
        'description' => 'Рекомендуется 64-х битная версия PHP',
        'check' => PHP_INT_MAX !== 2147483647,
        'mandatory' => false
    ],
    [
        'key' => 'dbversion',
        'currentValue' => $db_version,
        'requiredValue' => $getParam('required-db-version'),
        'apiRequiredValue' => '',
        'description' => '',
        'check' => $check_func('required-db-version', $db_version),
        'mandatory' => true
    ],
    [
        'key' => 'memory_limit',
        'currentValue' => ini_get('memory_limit'),
        'requiredValue' => $getParam('memory-limit'),
        'apiRequiredValue' => $getParam('memory-limit', 'api'),
        'description' => 'Править в ' . Html::tag('code', 'php.ini') .
            ' или в ' . Html::tag('code', '.htaccess'),
        'check' => $check_func('memory-limit'),
        'mandatory' => true
    ],
    [
        'key' => 'disk_free_space',
        'currentValue' => Yii::$app->formatter->asFilesize(disk_free_space($root_prefix)),
        'requiredValue' => $getParam('disk-free-space'),
        'apiRequiredValue' => $getParam('disk-free-space', 'api'),
        'description' => 'Рекомендуемое свободное место на диске.',
        'check' => $check_func('disk-free-space'),
        'mandatory' => false
    ],
    [
        'key' => 'upload_max_filesize',
        'currentValue' => ini_get('upload_max_filesize'),
        'requiredValue' => $getParam('upload-max-filesize'),
        'apiRequiredValue' => $getParam('upload-max-filesize', 'api'),
        'description' => 'Проверить не предполагает ли функционал загрузку файлов большего веса, чем указано тут. Править в ' .
            Html::tag('code', 'php.ini'),
        'check' => $check_func('upload-max-filesize'),
        'mandatory' => true
    ],
    [
        'key' => 'time_format',
        'currentValue' => Yii::$app->formatter->asDatetime($time) . '<br>' . Yii::$app->formatter->timeZone,
        'requiredValue' => date($datetimeFormat, $time) . '<br>' . date_default_timezone_get(),
        'apiRequiredValue' => '',
        'description' => Html::tag('code', $datetimeFormat) . '<br>' .
            'Если не стыкуется то можно изменить таймзону в' . '<br>' .
            Html::tag('code', 'common\\config\\main-local.php') . '<br>' .
            Html::tag(
                'code',
                '\'components\' => [\'formatter\' => [\'timeZone\' => \'Europe/Moscow\']]'
            ) . '<br>' .
            'и желательно поправить глобально в ' . Html::tag('code', 'php.ini') . '<br>' .
            Html::tag('code', 'date.timezone = Europe/Moscow')
        ,
        'check' => $check_func('time_format'),
        'mandatory' => false
    ],
]);
$gridViewDataProvider = new ArrayDataProvider(['allModels' => $data, 'pagination' => ['pageSize' => 10]]);
$this->registerCss(
    <<<CSS
.badge.text-center {
  display:inline-block;
  width:100%;
}
CSS
) ?>

<?= GridView::widget([
    'dataProvider' => $gridViewDataProvider,
    'columns' => [
        [
            'attribute' => 'check',
            'value' => static function ($data) {
                $options = ['class' => 'badge text-center'];
                if ($data['check'] === true) {
                    Html::addCssClass($options, 'bg-success');
                    return Html::tag('div', 'OK', $options);
                }
                if ($data['mandatory'] === true) {
                    Html::addCssClass($options, 'bg-danger');
                    return Html::tag('div', 'ERROR', $options);
                }
                Html::addCssClass($options, 'bg-warning');
                return Html::tag('div', 'WARN', $options);
            },
            'format' => 'raw'
        ],
        'key',
        'currentValue:html',
        'requiredValue:html',
        [
            'attribute' => 'apiRequiredValue',
            'contentOptions' => ['style' => 'min-width: 115px']
        ],
        [
            'attribute' => 'description',
            'format' => 'html',
            'contentOptions' => ['style' => 'white-space: pre-wrap;']
        ]
    ]
]) ?>
<h3>Config</h3>

<?= GridView::widget([
    'dataProvider' => new ArrayDataProvider([
        'allModels' => [
            ['name' => 'Cache', 'value' => Yii::$app->cache::class],
            ['name' => 'Queue', 'value' => Yii::$app->queue::class],
            ['name' => 'Session', 'value' => Yii::$app->session::class],
        ]
    ]),
    'columns' => ['name', 'value']
]) ?>

<?php
$envs = explode(
    "\n",
    str_replace(
        ["\r\n", "\n", "\r"],
        "\n",
        file_get_contents(dirname(__DIR__, 3) . '/.env.example')
    )
);
$allowedEnvs = [];
foreach ($envs as $env) {
    [$key] = explode('=', $env);
    if ($key) {
        $allowedEnvs[] = $key;
    }
}
$models = [];
foreach ($allowedEnvs as $allowedEnv) {
    $models[] = ['name' => $allowedEnv, 'value' => Environment::readEnv($allowedEnv)];
}
?>
<h3>Environment</h3>

<?= GridView::widget([
    'dataProvider' => new ArrayDataProvider(['allModels' => $models, 'pagination' => false]),
    'columns' => ['name', 'value']
]) ?>
