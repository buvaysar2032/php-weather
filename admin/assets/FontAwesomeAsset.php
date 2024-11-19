<?php

namespace admin\assets;

use yii\web\AssetBundle;

/**
 * Class FontAwesomeAsset
 *
 * @package admin\assets
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
class FontAwesomeAsset extends AssetBundle
{
    public $sourcePath = '@admin/assets/fontawesome';

    public $css = [YII_DEBUG ? 'css/all.css' : 'css/all.min.css'];

    public $js = [YII_DEBUG ? 'js/all.js' : 'js/all.min.js'];
}