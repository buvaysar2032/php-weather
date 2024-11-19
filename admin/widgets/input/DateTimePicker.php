<?php

namespace admin\widgets\input;

use kartik\datecontrol\DateControl;

/**
 * Пикер даты и времени
 *
 * @package admin\widgets\input
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 *
 * @property-read string $displayInput
 */
final class DateTimePicker extends DateControl
{
    /**
     * {@inheritdoc}
     */
    public function init(): void
    {
        $this->pluginOptions = ['todayHighlight' => true];
        $this->type = self::FORMAT_DATETIME;
        parent::init();
    }
}