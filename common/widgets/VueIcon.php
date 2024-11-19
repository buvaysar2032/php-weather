<?php

namespace common\widgets;

use yii\base\InvalidConfigException;
use yii\bootstrap5\Html;

/**
 * Icon widget that uses vue-fontawesome library
 *
 * Not suitable for pjax
 *
 * @package frontend\widgets
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
class VueIcon extends VueWidget
{
    public string $icon;

    /**
     * {@inheritdoc}
     * @throws InvalidConfigException
     */
    public function init(): void
    {
        if (!isset($this->icon)) {
            throw new InvalidConfigException('`icon` must be set');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function run(): string
    {
        return Html::tag('font-awesome-icon', '', ['icon' => $this->icon]);
    }
}