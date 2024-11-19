<?php

namespace admin\widgets\input;

use kartik\switchinput\SwitchInput;

/**
 * Переключатель поля "да/нет"
 *
 * @package admin\widgets\input
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
final class YesNoSwitch extends SwitchInput
{
    /**
     * {@inheritdoc}
     */
    public function init(): void
    {
        if (empty($this->containerOptions['style'])) {
            $this->containerOptions['style'] = ['display' => 'inline-block', 'margin-left' => '.5em'];
        }
        if (empty($this->pluginOptions['size'])) {
            $this->pluginOptions['size'] = 'mini';
        }
        if (empty($this->pluginOptions['onText'])) {
            $this->pluginOptions['onText'] = 'Да';
        }
        if (empty($this->pluginOptions['offText'])) {
            $this->pluginOptions['offText'] = 'Нет';
        }
        parent::init();
    }
}