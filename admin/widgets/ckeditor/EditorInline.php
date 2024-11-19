<?php

namespace admin\widgets\ckeditor;

use yii\base\InvalidConfigException;
use yii\bootstrap5\Html;

class EditorInline extends CKEditor5
{
    public string $editorType = 'Inline';

    /**
     * {@inheritdoc}
     * @throws InvalidConfigException
     */
    public function init(): void
    {
        parent::init();
        ob_start();
    }

    /**
     * {@inheritdoc}
     */
    protected function printEditorTag(): void
    {
        $value = ob_get_clean();
        print Html::tag('div', $value, $this->options);
    }
}
