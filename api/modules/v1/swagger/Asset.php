<?php

namespace api\modules\v1\swagger;

use yii\web\{AssetBundle, View};

/**
 * Class Asset
 *
 * @author m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
class Asset extends AssetBundle
{
    public $sourcePath = '@bower/swagger-ui/dist';

    public $js = [
        'swagger-ui-bundle.js',
        'swagger-ui-standalone-preset.js'
    ];

    public $jsOptions = ['position' => View::POS_HEAD];

    public $css = [
        ['swagger-ui.css', 'media' => 'screen, print']
    ];
}