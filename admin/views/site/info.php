<?php

use admin\widgets\bootstrap\Accordion;
use yii\bootstrap5\Html;

/**
 * @var $this yii\web\View
 */

$this->title = 'Информация о хостинге';

$db_version = Yii::$app->db->createCommand('SELECT VERSION()')->queryOne()['VERSION()'];
$db_variables = Yii::$app->db->createCommand('SHOW VARIABLES')->queryAll();
$innodb_buffer_pool_size = '';
foreach ($db_variables as $db_variable) {
    if ($db_variable['Variable_name'] === 'innodb_buffer_pool_size') {
        $innodb_buffer_pool_size = ($db_variable['Value'] / 1024 / 1024) . ' МБ';
        break;
    }
} ?>
<div class="site-info">
    <h1><?= Html::encode($this->title) ?></h1>
    <br>
    <?php $accordion = Accordion::begin() ?>
        <?= $accordion->item(
            [
                'id' => 'app_body',
                'title' => 'App Checkup',
                'headerTag' => 'h2',
                'panelOpen' => true,
                'content' => $this->render('_app_checkup_info', ['db_version' => $db_version])
            ]
        ) ?>

        <?= $accordion->item(
            [
                'id' => 'yii_body',
                'title' => 'Yii2 Checkup',
                'headerTag' => 'h2',
                'content' => $this->render('_yii2_checkup_info')
            ]
        ) ?>

        <?= $accordion->item(
            [
                'id' => 'phpini_body',
                'title' => 'PHPinfo',
                'headerTag' => 'h2',
                'content' => static function () {
                    ob_start();
                    phpinfo();
                    $phpinfo = ob_get_clean();
                    $phpinfo = preg_replace('%^.*<body>(.*)</body>.*$%ms', '$1', $phpinfo);
                    Yii::$app->view->registerCss(
                        <<<CSS
#phpinfo {}
#phpinfo pre {margin: 0; font-family: monospace;}
#phpinfo a:link {color: #009; text-decoration: none; background-color: #fff;}
#phpinfo a:hover {text-decoration: underline;}
#phpinfo table {color: var(--bs-dark); border-collapse: collapse; border: 0; width: 1144px; box-shadow: 1px 2px 3px #ccc;}
#phpinfo .center {text-align: center;}
#phpinfo .center table {margin: 1em auto; text-align: left;}
#phpinfo .center th {text-align: center !important;}
#phpinfo td, #phpinfo th {border: 1px solid #666; font-size: 75%; vertical-align: baseline; padding: 4px 5px;}
#phpinfo h1 {font-size: 150%;}
#phpinfo h2 {font-size: 125%;}
#phpinfo .p {text-align: left;}
#phpinfo .e {background-color: #ccf; width: 300px; font-weight: bold;}
#phpinfo .h {background-color: #99c; font-weight: bold;}
#phpinfo .v {background-color: #ddd; max-width: 850px; overflow-x: auto; word-wrap: break-word;}
#phpinfo .v i {color: #999;}
#phpinfo img {float: right; border: 0;}
#phpinfo hr {width: 934px; background-color: #ccc; border: 0; height: 1px;}
CSS
                    );
                    return <<<HTML
<div id='phpinfo'>
$phpinfo
</div>
HTML;
                }
            ]
        ) ?>
    <?php Accordion::end() ?>
</div>
