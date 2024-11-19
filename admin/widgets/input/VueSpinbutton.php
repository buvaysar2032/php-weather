<?php

namespace admin\widgets\input;

use yii\bootstrap5\{Html, InputWidget};

/**
 * Виджет VueJS поля для Yii2 формы
 *
 * @see     https://bootstrap-vue.org/docs/components/form-spinbutton
 * @package admin\widgets\input
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
final class VueSpinbutton extends InputWidget
{
    /**
     * Минимально возможное значение поля
     */
    public ?int $min = null;

    /**
     * Максимально возможное значение поля
     */
    public ?int $max = null;

    /**
     * Атрибуты передаваемые внутрь тега spinbutton
     */
    private array $_inputOptions = [];

    /**
     * {@inheritdoc}
     */
    public function init(): void
    {
        $this->_inputOptions['min'] = $this->min;
        $this->_inputOptions['max'] = $this->max;
    }

    /**
     * {@inheritdoc}
     */
    public function run(): string
    {
        if ($this->hasModel()) {
            $modelClassName = basename($this->model::class);
            $this->_inputOptions['name'] = sprintf("%s[%s]", $modelClassName, $this->attribute);
            $this->_inputOptions['id'] = strtolower($modelClassName) . '-' . $this->attribute;
            if ($this->model->{$this->attribute}) {
                $this->_inputOptions[':value'] = $this->model->{$this->attribute};
            }
        } else {
            $this->_inputOptions[':value'] = $this->value;
            $this->_inputOptions['name'] = $this->name;
            $this->_inputOptions['id'] = $this->id;
        }
        return Html::tag('b-form-spinbutton', '', $this->_inputOptions);
    }
}