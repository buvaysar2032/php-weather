<?php

/**
 * @var $this yii\web\View
 */

$root_prefix = Yii::getAlias('@root');
$frameworkPath = "$root_prefix/vendor/yiisoft/yii2";

if (!is_dir($frameworkPath)) {
    echo '<h1>Error</h1>';
    echo '<p><strong>The path to yii framework seems to be incorrect.</strong></p>';
}

require_once "$root_prefix/requirements/RequirementChecker.php";
$requirementsChecker = new RequirementChecker();

$this->registerCss(
    <<<CSS
#yii-requirements a {
  color: #0d6efd;
}
#yii-requirements a:hover {
  color: #0a58ca;
}
CSS
);
$requirementsChecker->checkYii()->render();
