<?php

namespace admin\widgets\tooltip;

use common\widgets\VueWidget;
use yii\bootstrap5\Html;

/**
 * Виджет для отображения подсказок
 *
 * Пример использования:
 * ```php
 * echo TooltipWidget::widget([
 *     'title' => 'Список возможных действий',
 *     'color' => 'white',
 *     'fontSize' => '10px'
 * ])
 * ```
 *
 * @package admin\widgets\tooltip
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
final class TooltipWidget extends VueWidget
{
    /**
     * Надпись, выводимая по наведению на иконку
     */
    public string $title = '';

    /**
     * Направление, с которого показывается tooltip
     *
     * Возможные значения:
     * top, bottom, left, right, auto, topleft, topright, bottomleft, bottomright, lefttop, leftbottom, righttop, rightbottom
     */
    public string $placement = 'top';

    /**
     * CSS размер шрифта для иконки с подсказкой
     */
    public string $fontSize;

    /**
     * Цвет иконки
     */
    public string $color;

    /**
     * {@inheritdoc}
     */
    public function run(): string
    {
        static $id = 0;
        $options = [
            'id' => "tooltip-$id",
            ':hidden' => '!tooltipsShow',
            'style' => [],
            'data-bs-toggle' => 'tooltip',
            'data-bs-placement' => $this->placement,
            'title' => $this->title
        ];
        if (isset($this->color)) {
            Html::addCssStyle($options, ['color' => $this->color]);
        }
        if (isset($this->fontSize)) {
            Html::addCssStyle($options, ['font-size' => $this->fontSize]);
        }
        $bIcon = Html::tag('font-awesome-icon', '', ['icon' => 'question-circle']);
        $icon = Html::tag('span', $bIcon, $options);
        $id++;
        return $icon;
    }
}