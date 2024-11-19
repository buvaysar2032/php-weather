<?php

namespace common\assets;

use common\components\helpers\UserFileHelper;
use Yii;
use yii\base\Exception;
use yii\helpers\{ArrayHelper, Json};
use yii\base\InvalidConfigException;
use yii\web\AssetBundle;

/**
 * Class BaseVueAsset
 *
 * @package common\assets
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
abstract class ViteAsset extends AssetBundle
{
    private const VITE_HOST = 'http://localhost:5133';

    public string $manifest = '@vue/dist/.vite/manifest.json';

    public $jsOptions = ['type' => 'module'];

    public array $modulePreloadOptions = [];

    /**
     * @var string[]
     */
    public array $vueJs;

    public array $modulePreloadJs = [];

    /**
     * @throws Exception
     */
    public function init(): void
    {
        $this->manifest = Yii::getAlias($this->manifest);
        $this->sourcePath = dirname($this->manifest, 2) . '/assets';
        if ($this->sourcePath) {
            UserFileHelper::createDirectory(Yii::getAlias($this->sourcePath));
        }
        $manifest = $this->getManifest();
        foreach ($this->vueJs as $js) {
            if (array_key_exists($js, $manifest)) {
                $this->addBundle($manifest[$js]);
            }
        }
        parent::init();
    }

    private function getManifest(): array
    {
        static $manifest;
        if ($manifest) {
            return $manifest;
        }
        if (file_exists($this->manifest)) {
            return $manifest = Json::decode(file_get_contents($this->manifest));
        }
        return [];
    }

    private function addBundle(array $bundle, bool $modulePreload = false): void
    {
        if (!$modulePreload && $this->isDev($bundle['src'])) {
            $this->js[] = $bundle['src'];
            return;
        }
        if (array_key_exists('css', $bundle)) {
            foreach ($bundle['css'] as $css) {
                $this->css[] = str_replace('assets/', '', $css);
            }
        }
        $jsPath = str_replace('assets/', '', $bundle['file']);
        if ($modulePreload) {
            if (!in_array($jsPath, $this->modulePreloadJs, true)) {
                $this->modulePreloadJs[] = $jsPath;
            }
        } elseif (!in_array($jsPath, $this->js, true)) {
            $this->js[] = $jsPath;
        }
        if (array_key_exists('imports', $bundle)) {
            $manifest = $this->getManifest();
            foreach ($bundle['imports'] as $import) {
                if (array_key_exists($import, $manifest)) {
                    $this->addBundle($manifest[$import], true);
                }
            }
        }
    }

    private function isDev(string $src): bool
    {
        static $exists = null;
        if ($exists !== null) {
            return $exists;
        }
        if (YII_ENV_PROD) {
            return false;
        }
        $handle = curl_init(self::VITE_HOST . '/' . $src);
        curl_setopt($handle, CURLOPT_NOBODY, true);
        curl_setopt($handle, CURLOPT_TIMEOUT, 1);
        curl_exec($handle);
        $error = curl_errno($handle);
        curl_close($handle);

        if ($exists = !$error) {
            $this->baseUrl = self::VITE_HOST . '/';
            $this->sourcePath = null;
        }
        return $exists;
    }

    /**
     * {@inheritdoc}
     * @throws InvalidConfigException
     * @throws \Exception
     */
    public function registerAssetFiles($view): void
    {
        $manager = $view->getAssetManager();
        foreach ($this->modulePreloadJs as $module) {
            if (is_array($module)) {
                $file = array_shift($module);
                $options = ArrayHelper::merge($this->modulePreloadOptions, $module);
                $options['href'] = $manager->getAssetUrl($this, $file, ArrayHelper::getValue($options, 'appendTimestamp'));
                $options['rel'] = 'modulepreload';
                $view->registerLinkTag($options);
            } elseif ($module !== null) {
                $options = $this->modulePreloadOptions;
                $options['href'] = $manager->getAssetUrl($this, $module);
                $options['rel'] = 'modulepreload';
                $view->registerLinkTag($options);
            }
        }
        parent::registerAssetFiles($view);
    }
}