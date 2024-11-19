<?php

namespace admin\widgets\bootstrap;

use Throwable;
use yii\bootstrap5\{Html, Widget};

/**
 * Виджет для отображения аккордеона
 *
 * Пример использования:
 * ```php
 * $accordion = Accordion::begin();
 * echo $accordion->item([
 *     'id' => 'app_body',
 *     'title' => 'App Checkup',
 *     'headerTag' => 'h2',
 *     'panelOpen' => true,
 *     'content' => $this->render('view')
 * ]);
 * Accordion::end();
 * ```
 *
 * @package admin\widgets\bootstrap
 * @see     https://getbootstrap.com/docs/5.2/components/accordion/
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
class Accordion extends Widget
{
    public static function begin($config = []): self
    {
        $widget = parent::begin($config);
        Html::addCssClass($widget->options, 'accordion');
        echo Html::beginTag('div', $widget->options);
        return $widget;
    }

    /**
     * @throws Throwable
     */
    public function item(array $config): string
    {
        $config['accordion'] = $this;
        return Collapse::widget($config);
    }

    public static function end(): self
    {
        $widget = parent::end();
        echo Html::endTag('div');
        return $widget;
    }
}