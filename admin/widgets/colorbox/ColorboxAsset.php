<?php

namespace admin\widgets\colorbox;

use Yii;
use yii\web\{AssetBundle, JqueryAsset};

class ColorboxAsset extends AssetBundle
{
    public $sourcePath = '@bower/jquery-colorbox';

    public $depends = [JqueryAsset::class];

    /**
     * {@inheritdoc}
     */
    public function init(): void
    {
        parent::init();

        $this->js[] = YII_DEBUG ? 'jquery.colorbox.js' : 'jquery.colorbox-min.js';
        $this->registerLanguageAsset();
    }

    protected function registerLanguageAsset(): void
    {
        $language = Yii::$app->language;
        if (!file_exists(Yii::getAlias("$this->sourcePath/i18n/jquery.colorbox-$language.js"))) {
            $language = substr($language, 0, 2);
            if (!file_exists(Yii::getAlias("$this->sourcePath/i18n/jquery.colorbox-$language.js"))) {
                return;
            }
        }
        $this->js[] = "i18n/jquery.colorbox-$language.js";
    }
}
