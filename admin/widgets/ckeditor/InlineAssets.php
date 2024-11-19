<?php

namespace admin\widgets\ckeditor;

use yii\web\AssetBundle;

class InlineAssets extends AssetBundle
{
    public $sourcePath = '@admin/widgets/ckeditor/ckeditor5/inline/';

    public $js = ['ckeditor.js'];
}