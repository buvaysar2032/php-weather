<?php

use yii\web\View;

/**
 * @var $this         View
 * @var $resourceType string
 */

$this->registerJsFile(Yii::$app->environment->BASE_URI . '/ckfinder/ckfinder.js', ['position' => View::POS_BEGIN]);
$type = lcfirst($resourceType);
$swatch = Yii::$app->themeManager->isDark ? 'b' : 'a';
$baseUrl = Yii::$app->request->baseUrl;
$this->registerJs(
    <<<JS
CKFinder.widget('ckfinder-widget-$type', {
  skin: 'jquery-mobile',
  swatch: '$swatch',
  width: '100%',
  height: 700,
  resourceType: '$resourceType',
  connectorPath: '$baseUrl/ckfinder.php'
});
JS
) ?>
<div id="ckfinder-widget-<?= $type ?>"></div>
