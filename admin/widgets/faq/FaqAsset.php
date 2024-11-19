<?php

namespace admin\widgets\faq;

use yii\bootstrap5\BootstrapAsset;
use yii\web\{AssetBundle, YiiAsset};

/**
 * Class FaqAsset
 *
 * @package admin\widgets\faq
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
class FaqAsset extends AssetBundle
{
    public $sourcePath = '@app/widgets/faq/assets';

    public $css = ['css/styles.css'];

    public $depends = [YiiAsset::class, BootstrapAsset::class];
}