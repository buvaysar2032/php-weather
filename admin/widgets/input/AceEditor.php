<?php

namespace admin\widgets\input;

use trntv\aceeditor\AceEditor as TrntvAceEditor;
use Yii;

/**
 * Class AceEditor
 *
 * @package admin\widgets\input
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
class AceEditor extends TrntvAceEditor
{
    /**
     * {@inheritdoc}
     */
    public $containerOptions = [
        'class' => 'ace_editor',
        'style' => 'width: 100%; min-height: 400px'
    ];

    /**
     * {@inheritdoc}
     */
    public function init(): void
    {
        if ($this->theme === 'github') {
            if (Yii::$app->themeManager->isDark) {
                $this->theme = 'one_dark';
            } else {
                $this->theme = 'chrome';
            }
        }
        parent::init();
    }
}