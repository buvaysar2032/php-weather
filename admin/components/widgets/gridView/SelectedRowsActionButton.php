<?php

namespace admin\components\widgets\gridView;

use yii\bootstrap5\{Html, Widget};

/**
 * Виджет кнопки для отправки запросов по каждой выделенной строчке
 *
 * @package admin\components\widgets\gridView
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
final class SelectedRowsActionButton extends Widget
{
    /**
     * ID GridView таблицы, где выделяются строчки
     */
    public string $gridViewId;

    /**
     * Текст на кнопке
     */
    public string $label = 'Action All';

    /**
     * Ссылка на экшен, куда будут отправлены запросы по выделенным колонкам
     */
    public string $action;

    /**
     * Класс кнопки
     */
    public ?string $class = null;

    /**
     * Data атрибуты кнопки
     */
    public string|array|null $data = null;

    /**
     * {@inheritdoc}
     */
    public function run(): string
    {
        return Html::button(
            $this->label,
            [
                'class' => 'btn ' . $this->class,
                'onclick' => <<<JS
let rows = $("#$this->gridViewId").yiiGridView("getSelectedRows");
const count = rows.length;
for (let i = 0; i < count; i++) {
  $.post("$this->action?id="+rows[i], null, () => {
    if (i >= count) {
      location.reload();
    }
  });
}
JS,
                'data' => $this->data
            ]
        );
    }
}
