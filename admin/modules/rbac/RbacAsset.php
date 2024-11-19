<?php

namespace admin\modules\rbac;

use yii\web\{AssetBundle, YiiAsset};

/**
 * Class RbacAsset
 *
 * @package admin\modules\rbac
 */
class RbacAsset extends AssetBundle
{
    public $sourcePath = '@admin/modules/rbac/assets';

    public $js = ['js/rbac.js'];

    public $css = ['css/rbac.css'];

    public $depends = [YiiAsset::class];
}
