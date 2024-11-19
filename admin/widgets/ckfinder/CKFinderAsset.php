<?php

namespace admin\widgets\ckfinder;

use yii\web\AssetBundle;

/**
 * Class CKFinderAsset
 *
 * @package admin\widgets\ckfinder\assets
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
class CKFinderAsset extends AssetBundle
{
    public $sourcePath = '@admin/widgets/ckfinder/assets';

    public $js = ['js/modal.js'];

    public $css = ['css/preview.scss'];
}