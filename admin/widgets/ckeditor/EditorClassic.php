<?php

namespace admin\widgets\ckeditor;

use yii\bootstrap5\Html;

class EditorClassic extends CKEditor5
{
    public string $editorType = 'Classic';

    /**
     * {@inheritdoc}
     */
    protected function printEditorTag(): void
    {
        if ($this->hasModel()) {
            print Html::activeTextarea($this->model, $this->attribute, $this->options);
        } else {
            print Html::textarea($this->name, $this->value, $this->options);
        }
    }
}
