<?php

namespace admin\widgets\input;

use kartik\select2\Select2 as KartikSelect2;
use Yii;
use yii\web\JsExpression;

/**
 * Select виджет с возможностью настройки динамического ajax поиска
 *
 * @package admin\widgets\input
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
final class Select2 extends KartikSelect2
{
    /**
     * Ссылка на получение отфильтрованного списка элементов
     */
    public ?string $url = null;

    /**
     * Текст, выводимый когда поле пусто
     */
    public ?string $placeholder = null;

    /**
     * Доступно ли пустое значение
     */
    public bool $nullAvailable = false;

    /**
     * Текст "пустого" значения
     */
    public string $nullText = ' ';

    /**
     * Доступна ли очистка поля в null значение
     */
    public bool $allowClear = false;

    /**
     * {@inheritdoc}
     */
    public function init(): void
    {
        if (isset($this->placeholder)) {
            $this->options['placeholder'] = $this->placeholder;
        }
        if (!$this->hideSearch && $this->url) {
            $this->pluginOptions = array_merge(
                [
                    'allowClear' => $this->allowClear,
                    'minimumInputLength' => 3,
                    'language' => [
                        'errorLoading' => new JsExpression(
                            '() => "' . Yii::t('app', 'Waiting for results...') . '"'
                        )
                    ],
                    'ajax' => [
                        'url' => $this->url,
                        'dataType' => 'json',
                        'delay' => 300,
                        'data' => new JsExpression('(params) => ({q:params.term})'),
                        'cache' => true
                    ],
                    'escapeMarkup' => new JsExpression('(markup) => markup'),
                    'templateResult' => new JsExpression('(data) => data.text'),
                    'templateSelection' => new JsExpression('(data) => data.text')
                ],
                $this->pluginOptions
            );
        } else {
            $this->pluginOptions = array_merge(['allowClear' => $this->allowClear], $this->pluginOptions);
        }
        if ($this->data && $this->nullAvailable) {
            $this->data = [null => $this->nullText] + $this->data;
        }
        parent::init();
    }
}