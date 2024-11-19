<?php

namespace admin\components\widgets\searchMenu;

use Yii;
use yii\bootstrap5\{Html, Widget};

/**
 * Виджет поля для поиска среди разделов меню
 *
 * @package admin\components\widgets\searchMenu
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
final class SearchMenu extends Widget
{
    public $id = 'menu-search';

    /**
     * {@inheritdoc}
     */
    public function run(): string
    {
        $this->view->registerJs(
            <<<JS
$('#$this->id').on('keyup', function () {
  let value = $(this).val().toLowerCase(),
    filterFunction = function () {
      $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
    }
  $('.nav.navbar-nav li:not(.skip-search)').filter(filterFunction)
  $('.nav.navbar-nav li.dropdown div.dropdown-menu a.dropdown-item').filter(filterFunction)
})
JS
        );
        $input = Html::input(
            'text',
            'menu-search',
            '',
            [
                'id' => $this->id,
                'class' => 'form-control',
                'placeholder' => Yii::t('app', 'Search section...'),
                'style' => ['width' => '100%']
            ]
        );
        return Html::tag('span', $input, ['style' => ['margin' => '0']]);
    }
}
