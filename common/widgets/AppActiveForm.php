<?php

namespace common\widgets;

use yii\bootstrap5\{ActiveForm, Html};

/**
 * Class AppActiveForm
 *
 * @package common\components
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
class AppActiveForm extends ActiveForm
{
    /**
     * The default field class name when calling [[field()]] to create a new field.
     *
     * @see fieldConfig
     */
    public $fieldClass = AppActiveField::class;

    /**
     * {@inheritdoc}
     */
    public function init(): void
    {
        $this->view->registerCss(
            <<<CSS
.required-label::after {
  content: '*';
  margin-left: 3px;
  font-weight: 400;
  font-family: SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
  color: tomato;
}
CSS
        );
        parent::init();
    }

    /**
     * Модификация css классов поля.
     *
     * Отображение звездочки у обязательных для заполнения полей
     * Добавление класса active в label тег, если поле не пустое
     */
    public function field($model, $attribute, $options = []): AppActiveField
    {
        $fieldName = preg_replace('/\[.*]/', '', $attribute);
        /** @var AppActiveField $parentField */
        $parentField = parent::field($model, $attribute, $options);

        if (!empty($model->$fieldName)) {
            Html::addCssClass($parentField->labelOptions, 'active');
        }

        foreach ($model->rules() as $rule) {
            $ruleRequired = $rule[1] === 'required';
            if ($ruleRequired && !empty($rule['on'])) {
                $ruleRequired &= in_array($model->scenario, (array)$rule['on'], true);
            }
            if ($ruleRequired && !empty($rule['except'])) {
                $ruleRequired &= !in_array($model->scenario, (array)$rule['except'], true);
            }
            if (!$ruleRequired) {
                continue;
            }
            if (is_array($rule[0])) {
                foreach ($rule[0] as $field) {
                    if ($field === $fieldName) {
                        Html::addCssClass($parentField->labelOptions, 'required-label');
                        break 2;
                    }
                }
            } elseif ($rule[0] === $fieldName) {
                Html::addCssClass($parentField->labelOptions, 'required-label');
                break;
            }
        }
        return $parentField;
    }
}
