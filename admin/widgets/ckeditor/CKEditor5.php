<?php

namespace admin\widgets\ckeditor;

use Exception;
use Yii;
use yii\base\InvalidConfigException;
use yii\helpers\Json;
use yii\web\{JsExpression, View};
use yii\widgets\InputWidget;

abstract class CKEditor5 extends InputWidget
{
    public string $editorType;

    public array $clientOptions = [];

    /**
     * Toolbar options array
     */
    public array $toolbar = [];

    /**
     * Url to image upload
     */
    public string $uploadUrl = '/admin/ckfinder.php?command=QuickUpload&type=Images&responseType=json';

    /**
     * {@inheritdoc}
     *
     * @throws InvalidConfigException
     * @throws Exception
     */
    public function run(): void
    {
        $this->registerAssets($this->getView());
        $this->registerEditorJS();
        $this->printEditorTag();
    }

    /**
     * @throws InvalidConfigException
     * @throws Exception
     */
    protected function registerAssets(View $view): void
    {
        $view->registerCss(
            <<<CSS
.ck.ck-content.ck-editor__editable {
  color: #303030
}
CSS
        );
        $view->registerJsFile('/ckfinder/ckfinder.js');
        $assets = match ($this->editorType) {
            'Classic' => ClassicAssets::register($view),
            'Balloon' => BalloonAssets::register($view),
            'Inline' => InlineAssets::register($view),
            default => [],
        };

        if (array_key_exists('language', $this->clientOptions)) {
            $assets->js[] = 'translations/' . $this->clientOptions['language'] . '.js';
        }
    }

    /**
     * Registration JS
     *
     * @throws Exception
     */
    protected function registerEditorJS(): void
    {
        if (!empty($this->toolbar)) {
            $this->clientOptions['toolbar'] = $this->toolbar;
        }
        if (empty($this->clientOptions['ckfinder']) && !empty($this->uploadUrl)) {
            $this->clientOptions['ckfinder']['uploadUrl'] = $this->uploadUrl;
            $this->clientOptions['ckfinder']['options']['skin'] = 'jquery-mobile';
            $this->clientOptions['ckfinder']['options']['swatch'] = Yii::$app->themeManager->isDark ? 'b' : 'a';
            $this->clientOptions['ckfinder']['options']['resourceType'] = 'Images';
            $this->clientOptions['ckfinder']['options']['connectorPath'] = '/admin/ckfinder.php';
        }
        $encodedOptions = Json::htmlEncode($this->clientOptions);

        $js = new JsExpression(
            <<<JS
var clientOptions = JSON.parse('$encodedOptions');
if (typeof $this->editorType == 'undefined' || !$this->editorType) {
  var $this->editorType = {}
}
var elem = document.querySelector('#{$this->options['id']}')
{$this->editorType}Editor.create(elem, clientOptions).catch(error => {console.error(error);});
elem.removeAttribute('data-ckeditor-type')
elem.removeAttribute('data-ckeditor-options')
JS
        );
        $this->view->registerJs($js);
        $this->options['data-ckeditor-type'] = $this->editorType;
        $this->options['data-ckeditor-options'] = $encodedOptions;
    }

    /**
     * View tag for editor
     */
    protected function printEditorTag(): void
    {
    }
}
