<?php

namespace admin\widgets\input;

use kartik\datecontrol\DateControl;

/**
 * Пикер времени
 *
 * @package admin\widgets\input
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
final class TimePicker extends DateControl
{
    /**
     * {@inheritdoc}
     */
    public function init(): void
    {
        $this->type = self::FORMAT_TIME;
        $this->displayTimezone = 'UTC';
        $this->saveTimezone = 'UTC';
        parent::init();
    }
}