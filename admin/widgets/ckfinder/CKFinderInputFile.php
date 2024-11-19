<?php

namespace admin\widgets\ckfinder;

use common\components\{helpers\UserFileHelper, helpers\UserUrl};
use Exception;
use Yii;
use yii\base\InvalidConfigException;
use yii\bootstrap5\Html;
use yii\web\View;
use yii\widgets\InputWidget;

/**
 * Виджет выбора файла в файловом менеджере CKFinder
 *
 * @package admin\widgets\ckfinder
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
class CKFinderInputFile extends InputWidget
{
    /**
     * Выбираем ли только картинку
     */
    public bool $isImage = true;

    /**
     * Шаблон
     */
    public string $template;

    /**
     * Значение по умолчанию
     */
    public ?string $defaultValue = null;

    /**
     * Текст кнопки "Выбрать"
     */
    public string $buttonName;

    /**
     * Опции передаваемые в [[Html::button()]]
     */
    public array $buttonOptions = [];

    /**
     * JS функция вызываемая после выбора файла
     */
    public string $onChangeCallback;

    /**
     * Тип ресурса из /htdocs/ckfinder/config.php
     */
    public string $resourceType = 'Images';

    public string $startupPath;

    /**
     * {@inheritdoc}
     *
     * @throws InvalidConfigException
     */
    public function init(): void
    {
        parent::init();
        $this->view->registerJsFile('/ckfinder/ckfinder.js', ['position' => View::POS_BEGIN]);
        CKFinderAsset::register($this->view);

        $this->buttonOptions['id'] = $this->options['id'] . '-btn';
        $errorClass = '';
        $previewUrl = '#';
        if ($this->hasModel()) {
            $htdocs = Yii::getAlias('@root/htdocs');
            $file = $this->model->{preg_replace('/\[.*]/', '', $this->attribute)};
            if (is_string($file)) {
                if (file_exists($htdocs . $file) && !is_dir($htdocs . $file)) {
                    $this->defaultValue = $file;
                    $previewUrl = $file . '?_t=' . filectime($htdocs . $file);
                } elseif (preg_match('/^https?:\/\//', $file)) {
                    $previewUrl = $file;
                }
            }
            $errorClass = ($this->model->hasErrors($this->attribute)) ? 'has-error' : '';
        }
        if ($this->isImage) {
// Если выбираем только изображения, то вставка миниатюры по умолчанию
            $img = Html::img(
                $previewUrl,
                ['id' => $this->options['id'] . '-preview', 'class' => 'preview-image', 'alt' => ' ']
            );
            $img = Html::tag('div', $img, [
                'class' => 'input-group-text',
                'style' => ['width' => '20%', 'position' => 'relative', 'overflow' => 'hidden']
            ]);
        } else {
            $img = '';
        }
        if (empty($this->buttonName)) {
            $this->buttonName = Yii::t('app', 'Browse');
        }
        $this->template = <<<HTML
<div class="file-input input-group input-group_inner $errorClass" style="flex-wrap: nowrap">
$img{input}{button}
</div>
HTML;
    }

    /**
     * {@inheritdoc}
     * @throws Exception
     */
    public function run(): string
    {
        Html::addCssStyle($this->options, ['width' => '100%', 'flex' => '0 1 auto']);
        if (!empty($this->onChangeCallback)) {
            $this->options['onchange'] = $this->onChangeCallback;
        }
        if ($this->hasModel()) {
            $replace['{input}'] = Html::activeTextInput($this->model, $this->attribute, $this->options);
        } else {
            $replace['{input}'] = Html::textInput($this->name, $this->defaultValue, $this->options);
        }
        Html::addCssClass(
            $this->buttonOptions,
            ['btn', 'btn-sm', 'btn-outline-secondary', 'file-input', 'input-group-text']
        );
        Html::addCssStyle($this->buttonOptions, ['flex' => '0 1 auto']);
        if (!isset($this->buttonOptions['disabled'])) {
            $this->buttonOptions['disabled'] = $this->field->inputOptions['disabled'] ?? null;
        }
        $callback = ', ' . (!empty($this->onChangeCallback) ? $this->onChangeCallback : 'undefined');
        $startupPath = '';
        if (!empty($this->startupPath) && !empty($this->resourceType)) {
            $startupPath = trim($this->startupPath, '/');
            UserFileHelper::createDirectory(
                sprintf(
                    '%s%s/%s/%s',
                    Yii::getAlias('@root/htdocs'),
                    UserUrl::UPLOADS,
                    strtolower($this->resourceType),
                    $startupPath
                )
            );
            $startupPath = ", '$this->resourceType:/$startupPath/'";
        }
        $strIsImage = $this->isImage ? 'true' : 'false';
        $swatch = Yii::$app->themeManager->isDark ? 'b' : 'a';
        $baseUrl = Yii::$app->request->baseUrl;
        $replace['{button}'] = Html::button(
            $this->buttonName,
            array_merge(
                $this->buttonOptions,
                ['onclick' => "selectFileWithCKFinder('$baseUrl', '{$this->options['id']}', $strIsImage, '$this->resourceType', '$swatch'$callback$startupPath)"]
            )
        );
        return strtr($this->template, $replace);
    }
}
