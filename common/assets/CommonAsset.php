<?php

namespace common\assets;

use yii\bootstrap5\BootstrapAsset;
use yii\web\{AssetBundle, YiiAsset};

/**
 * Class CommonAsset
 *
 * @package common\assets
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
class CommonAsset extends AssetBundle
{
    public $sourcePath = '@common/assets/source';

    public $css = ['styles/main.scss'];

    public $depends = [YiiAsset::class, BootstrapAsset::class];
}