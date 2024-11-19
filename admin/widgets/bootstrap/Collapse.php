<?php

namespace admin\widgets\bootstrap;

use Closure;
use yii\bootstrap5\{Html, Widget};

/**
 * Виджет для отображения разворачиваемого блока аккордеона
 *
 * Пример использования:
 *
 * ```php
 * echo Collapse::widget([
 *     'id' => 'collapse-main',
 *     'title' => 'Основные данные',
 *     'content' => $this->render(
 *         '_page_main_form',
 *         [
 *             'model' => $model,
 *             'form' => $form,
 *         ]
 *     )
 * ]);
 * ```
 *
 * @package admin\widgets\bootstrap
 * @see     https://getbootstrap.com/docs/5.2/components/collapse/
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
final class Collapse extends Widget
{
    /**
     * Идентификатор панели для открытия
     */
    public $id;

    /**
     * Заголовок панели
     */
    public string $title;

    /**
     * Настройки внешнего контейнера
     */
    public array $containerOptions = [];

    /**
     * Настройки контейнера заголовка
     */
    public array $headerOptions = [];

    /**
     * Настройки переключателя
     */
    public array $toggleOptions = [];

    /**
     * Настройки контейнера разворачиваемого контента
     */
    public array $targetOptions = [];

    /**
     * Тег заголовка
     */
    public string $headerTag = 'h4';

    /**
     * Содержание панели
     */
    public string|Closure $content;


    /**
     * Панель открыта по умолчанию
     */
    public bool $panelOpen = false;

    /**
     * Название аккордеона
     */
    public Accordion $accordion;

    /**
     * {@inheritdoc}
     */
    public function init(): void
    {
        $this->initDefaultOptions();
        $this->initAccordion();

        parent::init();
    }

    /**
     * Настройка по умолчанию
     */
    private function initDefaultOptions(): void
    {
        static $id;
        if (!isset($this->id)) {
            $this->id = $id ?? 0;
            // Увеличиваем индекс для сохранения уникальности id collapse панелей
            $id = ++$this->id;
            $this->id = "collapse_$this->id";
        }

        $this->toggleOptions = [
            'data' => [
                'bs-toggle' => 'collapse',
                'bs-target' => "#$this->id"
            ],
            'aria' => [
                'expanded' => $this->panelOpen ? 'true' : 'false',
                'controls' => $this->id
            ],
            'role' => 'button'
        ];
        $this->targetOptions = ['id' => $this->id, 'class' => ['card-body', 'collapse']];

        if ($this->panelOpen) {
            Html::addCssClass($this->targetOptions, 'show');
        } else {
            Html::addCssClass($this->toggleOptions, 'collapsed');
        }
    }

    /**
     * Настройка для работы в аккордеоне
     */
    private function initAccordion(): void
    {
        if (isset($this->accordion)) {
            Html::addCssClass($this->containerOptions, 'accordion-item');
            Html::addCssClass($this->headerOptions, 'accordion-header');
            Html::addCssClass($this->toggleOptions, 'accordion-button');
            Html::addCssClass($this->targetOptions, 'accordion-collapse accordion-body');
            $this->headerOptions['id'] = "heading_$this->id";
            $this->targetOptions['aria-labelledby'] = $this->headerOptions['id'];
            $this->targetOptions['data-bs-parent'] = "#{$this->accordion->id}";
        } else {
            Html::addCssClass($this->containerOptions, 'card');
            Html::addCssClass($this->headerOptions, 'card-header');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function run(): string
    {
        echo Html::beginTag('div', $this->containerOptions);
        echo $this->generateTriggerHeader();
        echo $this->generateTargetPanel();
        return Html::endTag('div');
    }

    /**
     * Генерация контента для заголовка
     */
    private function generateTriggerHeader(): string
    {
        $a = Html::tag('a', $this->title, $this->toggleOptions);
        return Html::tag($this->headerTag, $a, $this->headerOptions);
    }

    /**
     * Генерация контента коллапса
     */
    private function generateTargetPanel(): string
    {
        echo Html::beginTag('div', $this->targetOptions);
        // Вызов пользовательской функции, которая может возвращать свои значения через echo
        if (is_callable($this->content)) {
            echo call_user_func($this->content);
        } else {
            echo $this->content;
        }
        return Html::endTag('div');
    }
}