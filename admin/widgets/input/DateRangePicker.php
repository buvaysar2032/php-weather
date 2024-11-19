<?php

namespace admin\widgets\input;

use kartik\daterange\DateRangePicker as KartikDateRangePicker;
use Yii;
use yii\web\JsExpression;

/**
 * Пикер периода времени
 *
 * @package admin\widgets\input
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
final class DateRangePicker extends KartikDateRangePicker
{
    /**
     * {@inheritdoc}
     */
    public function init(): void
    {
        $this->language = 'ru';
        $this->setLanguage('', Yii::getAlias('@root/vendor/kartik-v/yii2-date-range/src/assets'));
        if (!isset($this->pluginOptions['timePicker'])) {
            $this->pluginOptions['timePicker'] = true;
        }
        if (!isset($this->pluginOptions['timePicker24Hour'])) {
            $this->pluginOptions['timePicker24Hour'] = true;
        }
        if (empty($this->pluginOptions['locale']['format'])) {
            $this->pluginOptions['locale']['format'] = (!$this->pluginOptions['timePicker']) ? 'DD.MM.Y' : 'DD.MM.Y HH:mm';
        }
        if (!isset($this->pluginEvents['cancel.daterangepicker'])) {
            $this->pluginEvents['cancel.daterangepicker'] = new JsExpression(
                "function(ev, picker) {let e13=$.Event('keydown');e13.keyCode=13;let _input=$(this);if(!$(this).is('input')){_input=$(this).parent().find('input:hidden');}_input.val('').trigger(e13);}"
            );
        }
        parent::init();
    }

    /**
     * {@inheritdoc}
     */
    public function setLanguage($prefix, $assetPath = null, $filePath = null, $suffix = '.js'): void
    {
        if (empty($this->_langFile)) {
            parent::setLanguage($prefix, $assetPath, $filePath, $suffix);
        }
    }
}