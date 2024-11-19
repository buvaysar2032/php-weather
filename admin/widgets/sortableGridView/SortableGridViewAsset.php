<?php

namespace admin\widgets\sortableGridView;

use yii\bootstrap5\{BootstrapAsset, BootstrapPluginAsset};
use yii\jui\JuiAsset;
use yii\web\{AssetBundle, JqueryAsset};

/**
 * Class SortableGridViewAsset
 * @package admin\widgets\sortableGridView
 * @author m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
class SortableGridViewAsset extends AssetBundle
{
    public $sourcePath = '@admin/widgets/sortableGridView/assets';

    public $baseUrl = '@web';

    public $js = ['js/jquery.ui.touch-punch.min.js', 'js/sortable-grid-view.js'];

    public $depends = [JqueryAsset::class, JuiAsset::class, BootstrapAsset::class, BootstrapPluginAsset::class];
}