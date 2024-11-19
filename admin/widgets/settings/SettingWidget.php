<?php

namespace admin\widgets\settings;

use common\models\Setting;
use kartik\icons\Icon;
use Yii;
use yii\base\InvalidConfigException;
use yii\bootstrap5\{Html, Widget};
use yii\helpers\Url;
use yii\web\{NotFoundHttpException, View};

/**
 * Виджет для изменения настроек в таблице `settings`
 *
 * Пример использования:
 * ```php
 * echo SettingWidget::widget(
 *     [
 *         'title' => 'Максимальное число очков за сессию',
 *         'type' => 'number',
 *         'name_id' => 'max_score'
 *     ]
 * );
 * ```
 *
 * @package admin\widgets\settings
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
final class SettingWidget extends Widget
{
    /**
     * Тип поля - текст
     */
    public const TYPE_TEXT = 'text';

    /**
     * Тип поля - число
     */
    public const TYPE_NUMBER = 'number';

    /**
     * Тип поля - пароль
     */
    public const TYPE_PASSWORD = 'password';

    /**
     * Тип поля - email
     */
    public const TYPE_EMAIL = 'email';

    /**
     * Тип поля - чекбокс
     */
    public const TYPE_CHECKBOX = 'checkbox';

    /**
     * Надпись, выводимая рядом с полем
     */
    public string $title = 'Настройки';

    /**
     * Тип поля, возможные значения: text, number, email, checkbox
     */
    public string $type = self::TYPE_TEXT;

    /**
     * Название параметра в настройках
     */
    public string $name_id = 'setting';

    /**
     * Ссылка на экшен изменения настроек
     */
    private ?string $_url;

    /**
     * {@inheritdoc}
     */
    public function init(): void
    {
        $this->_url = Url::to(['/setting/change-parameter']);
        parent::init();
    }

    /**
     * {@inheritdoc}
     */
    public function getView(): View
    {
        return Yii::$app->view;
    }

    /**
     * {@inheritdoc}
     *
     * @throws InvalidConfigException
     * @throws NotFoundHttpException
     */
    public function run(): string
    {
        if (!in_array(
            $this->type,
            [self::TYPE_TEXT, self::TYPE_NUMBER, self::TYPE_CHECKBOX, self::TYPE_EMAIL, self::TYPE_PASSWORD]
        )) {
            $this->type = self::TYPE_TEXT; // Если ввели неверный тип, то сбрасываем на значение по умолчанию
        }
        return match ($this->type) {
            self::TYPE_CHECKBOX => $this->renderCheckbox(),
            default => $this->renderInput(),
        };
    }

    /**
     * @throws NotFoundHttpException
     */
    private function renderCheckbox(): string
    {
        $this->getView()->registerJs(
            <<<JS
$('#$this->name_id').on('click', function() {
  $.ajax({
    url: '$this->_url',
    type: 'POST',
    data: {
      param:'$this->name_id',
      value: $(this).is(':checked') ? 1 : 0
    },
    success: function (res) {
        console.log(res)
    },
    error: function () {
        alert('Error!')
    }
  })
})
JS
        );
        $checkbox = Html::checkbox(
            $this->name_id,
            (int)Setting::getParameterValue($this->name_id) === 1,
            ['id' => $this->name_id]
        );

        $label = Html::label($this->title, $this->name_id);

        return Html::tag('div', $checkbox . $label);
    }

    /**
     * @throws InvalidConfigException
     * @throws NotFoundHttpException
     */
    private function renderInput(): string
    {
        $this->getView()->registerJs(
            <<<JS
$('#$this->name_id-btn').on('click', function() {
  $.ajax({
    url: '$this->_url',
    type: 'POST',
    data: {
      param:'$this->name_id',
      value: $('#$this->name_id').val()
    },
    success: function (res) {
      console.log(res)
    },
    error: function () {
      alert('Error!')
    }
  })
})
JS
        );

        $input = Html::input(
            $this->type,
            $this->name_id,
            Setting::getParameterValue($this->name_id),
            ['id' => $this->name_id, 'class' => 'form-control']
        );

        $label = Html::label($this->title, $this->name_id);

        $saveButton = Html::button(
            Icon::show('save'),
            ['id' => "$this->name_id-btn", 'class' => ['btn', 'btn-success', 'input-group-text']]
        );

        return $label . Html::tag('div', $input . $saveButton, ['class' => ['input-group']]);
    }
}