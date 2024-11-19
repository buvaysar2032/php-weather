<?php
/**
 * @link      https://github.com/wbraganca/yii2-dynamicform
 * @copyright Copyright (c) 2014 Wanderson Bragança
 * @license   https://github.com/wbraganca/yii2-dynamicform/blob/master/LICENSE
 */

namespace admin\widgets\dynamicForm;

use yii\web\{AssetBundle, JqueryAsset};
use yii\widgets\ActiveFormAsset;

/**
 * Asset bundle for dynamicform Widget
 *
 * @package admin\widgets\dynamic_form
 * @author  Wanderson Bragança <wanderson.wbc@gmail.com>
 */
class DynamicFormAsset extends AssetBundle
{
    public $depends = [JqueryAsset::class, ActiveFormAsset::class];

    /**
     * {@inheritdoc}
     */
    final public function init(): void
    {
        $this->setSourcePath(__DIR__ . '/assets');
        $this->setupAssets('js', ['yii2-dynamic-form']);
        parent::init();
    }

    /**
     * Sets the source path if empty
     *
     * @param string $path the path to be set
     */
    private function setSourcePath(string $path): void
    {
        if (empty($this->sourcePath)) {
            $this->sourcePath = $path;
        }
    }

    /**
     * Set up CSS and JS asset arrays based on the base-file names
     *
     * @param string $type  whether 'css' or 'js'
     * @param array  $files the list of 'css' or 'js' basefile names
     */
    private function setupAssets(string $type, array $files = []): void
    {
        $srcFiles = [];
        $minFiles = [];
        foreach ($files as $file) {
            $srcFiles[] = "$file.$type";
            $minFiles[] = "$file.min.$type";
        }
        if (empty($this->$type)) {
            $this->$type = YII_DEBUG ? $srcFiles : $minFiles;
        }
    }
}
