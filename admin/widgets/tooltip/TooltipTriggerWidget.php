<?php

namespace admin\widgets\tooltip;

use yii\bootstrap5\Widget;

/**
 * Кнопка для включения/выключения подсказок
 *
 * @package admin\widgets\tooltip
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
final class TooltipTriggerWidget extends Widget
{
    /**
     * {@inheritdoc}
     */
    public function run(): string
    {
        return <<<HTML
<div class="form-check form-switch">
    <input class="form-check-input" type="checkbox" id="tooltipSwitch" v-model="tooltipsShow"/>
    <label class="form-check-label" for="tooltipSwitch">
        Подсказки <font-awesome-icon icon="question-circle"></font-awesome-icon>
    </label>
</div>
HTML;
    }
}