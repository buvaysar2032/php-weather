<?php

namespace admin\components\widgets\gridView;

use kartik\grid\CheckboxColumn as KartikCheckboxColumn;

/**
 * CheckBoxColumn array widget
 *
 * Колонка для выделения строк таблицы
 *
 * @package admin\components\widgets\gridView
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
final class CheckBoxColumn extends ColumnWidget
{
    /**
     * {@inheritdoc}
     */
    public function run(): array
    {
        return [
            'class' => KartikCheckboxColumn::class,
            'headerOptions' => ['class' => 'kartik-sheet-style']
        ];
    }
}
