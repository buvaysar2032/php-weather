<?php

namespace admin\widgets\faq;

use admin\widgets\bootstrap\Collapse;
use common\components\helpers\UserUrl;
use Exception;
use Throwable;
use yii\bootstrap5\{Html, Widget};

/**
 * Виджет для отображения часто задаваемых вопросов с ответами
 *
 * Пример использования виджета во view-файле:
 * ```php
 * echo FaqWidget::widget([
 *     'config' => [
 *         'Вопрос 1' => 'Ответ 1',
 *         'Вопрос 2' => 'Ответ 2',
 *         'Вопрос 3' => 'Ответ 3',
 *     ]
 *     'item' => 'lottery' //Указывается при добавлении нескольких виджетов на страницу
 * ]);
 * ```
 *
 * @package admin\widgets\faq
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
final class FaqWidget extends Widget
{
    /**
     * Конфигурация виджета. В качестве ключей выступают вопросы, а в качестве значений - ответы.
     */
    public array $config = [];

    /**
     * Название сущности. Позволяет задавать уникальные id для элементов в случаях, когда виджет используется более одного раза на странице.
     */
    public string $item = 'faq_item';

    /**
     * {@inheritdoc}
     */
    public function init(): void
    {
        parent::init();
        FaqAsset::register($this->view);
    }

    /**
     * {@inheritdoc}
     * @throws Exception|Throwable
     */
    public function run(): string
    {
        $faqConfig = $this->config;
        $html = '';
        if (count($faqConfig) === 0) {
            return $html;
        }
        $i = 0;
        foreach ($faqConfig as $question => $answer) {

            $html .= Collapse::widget(
                ['title' => $question, 'id' => $this->item . $i, 'content' => $answer]
            );
            $i++;
        }
        return $html;
    }
}