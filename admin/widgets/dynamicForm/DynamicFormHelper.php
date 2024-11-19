<?php

namespace admin\widgets\dynamicForm;

use kartik\icons\Icon;
use yii\base\{Component, InvalidConfigException};
use yii\bootstrap5\Html;
use yii\db\ActiveRecord;

/**
 * Class DynamicFormHelper
 *
 * @package admin\widgets\dynamic_form
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
class DynamicFormHelper extends Component
{
    /**
     * Кнопка "добавить форму"
     *
     * @throws InvalidConfigException
     */
    public static function plusButton(string $class): string
    {
        return Html::button(
            Icon::show('plus'),
            ['class' => [$class, 'btn', 'btn-success', 'btn-sm'], 'title' => 'Добавить']
        );
    }

    /**
     * Кнопка "удалить форму"
     *
     * @throws InvalidConfigException
     */
    public static function minusButton(string $class): string
    {
        return Html::button(
            Icon::show('minus'),
            ['class' => [$class, 'btn', 'btn-danger', 'btn-sm'], 'title' => 'Удалить']
        );
    }

    /**
     * Скрытое поле с главным ключом модели для работы update
     */
    public static function primaryKeyHiddenInput(ActiveRecord $model, string $attribute = 'id'): string
    {
        if (!$model->isNewRecord) {
            return Html::activeHiddenInput($model, $attribute);
        }
        return '';
    }
}