<?php

namespace admin\widgets\input;

use kartik\datecontrol\DateControl;

/**
 * Пикер даты
 *
 * @package admin\widgets\input
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 *
 * @property-read string $displayInput
 */
final class DatePicker extends DateControl
{
    /**
     * {@inheritdoc}
     */
    public function init(): void
    {
        $this->pluginOptions = ['todayHighlight' => true];
        $this->type = self::FORMAT_DATE;
        parent::init();
    }
}