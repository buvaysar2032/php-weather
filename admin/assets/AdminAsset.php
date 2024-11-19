<?php

namespace admin\assets;

use common\assets\CommonAsset;
use yii\web\AssetBundle;

/**
 * Main admin application asset bundle.
 *
 * @package admin\assets
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
class AdminAsset extends AssetBundle
{
    public $sourcePath = '@admin/assets/source';

    public $css = ['styles/site.scss'];

    public $js = ['js/scripts.js'];

    public $depends = [CommonAsset::class];
}
