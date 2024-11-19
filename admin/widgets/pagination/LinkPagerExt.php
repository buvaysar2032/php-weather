<?php

namespace admin\widgets\pagination;

use admin\widgets\input\Select2;
use Throwable;
use Yii;
use yii\bootstrap5\{Html, LinkPager};

/**
 * Пагинатор LinkPager
 *
 * Позволяет переключаться на конкретную страницу с заданным количеством записей на странице
 *
 * @package admin\widgets\pagination
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
final class LinkPagerExt extends LinkPager
{
    /**
     * '{pageButtons} {customPage} {customPageSize}' custom button will be auto generate if `{customPage}` or `{customPageSize}` is existed
     */
    public string $template = '<div class="row">{pageButtons}{customPage}{customPageSize}{customButton}</div>';

    /**
     * Параметры контейнера полей ввода
     */
    public array $customGroupContainerOptions = ['class' => ['col']];

    /**
     * Параметры полей ввода
     */
    public array $customGroupInputOptions = ['class' => 'form-control'];

    /**
     * Параметры контейнера
     */
    public array $customButtonContainerOptions = ['class' => 'col-2'];

    /**
     * Параметры кнопки
     */
    public array $customButtonOptions = ['class' => ['btn', 'btn-primary']];

    /**
     * Минимальный размер страницы
     */
    public int $minPageSize = 10;

    /**
     * Максимальный размер страницы
     */
    public int $maxPageSize = 50;

    /**
     * Label размера страницы
     */
    public string $pageSizeLabel = 'Строк';

    /**
     * Label номера страницы
     */
    public string $pageLabel = 'Стр.';

    /**
     * Label кнопки перехода
     */
    public string $submitButtonLabel = 'Перейти';

    /**
     * ID кнопки перехода
     */
    private ?string $_submitButtonId;

    /**
     * Название поля ввода размера страницы
     */
    private ?string $_pageSizeInputName;

    /**
     * Название поля ввода страницы
     */
    private ?string $_pageInputName;

    /**
     * {@inheritdoc}
     */
    public function init(): void
    {
        parent::init();
        $this->_submitButtonId = "submit-button-$this->id";
        $this->_pageInputName = "page-input-$this->id";
        $this->_pageSizeInputName = "page-size-input-$this->id";
        $this->registerJs();
    }

    /**
     * {@inheritdoc}
     *
     * @throws Throwable
     */
    public function run(): string
    {
        if ($this->registerLinkTags) {
            $this->registerLinkTags();
        }
        return $this->renderPageContent();
    }

    /**
     * Рендер виджета
     *
     * @throws Throwable
     */
    private function renderPageContent(): string
    {
        $pageCount = $this->pagination->getPageCount();
        if ($pageCount < 2 && $this->hideOnSinglePage) {
            return '';
        }
        $useCustomer = false;
        return preg_replace_callback(
            '/{([\w\-\/]+)}/',
            function ($matches) use (&$useCustomer) {
                $name = $matches[1];
                $result = '';
                if ($name === 'pageButtons') {
                    $result = $this->renderPageButtons();
                } elseif ($name === 'customPage') {
                    $useCustomer = true;
                    $result = $this->renderCustomPage();
                } elseif ($name === 'customPageSize') {
                    $useCustomer = true;
                    $result = $this->renderCustomPageSize();
                } elseif ($name === 'customButton' && $useCustomer) {
                    $result = $this->renderCustomButton();
                }
                return $result;
            },
            $this->template
        );
    }

    /**
     * {@inheritdoc}
     */
    public function renderPageButtons(): string
    {
        return Html::tag('div', parent::renderPageButtons(), ['class' => 'col-lg-6']);
    }

    /**
     * Рендер поля ввода номера страницы
     *
     * @throws Throwable
     */
    private function renderCustomPage(): string
    {
        $page = 1;
        $params = Yii::$app->getRequest()->queryParams;
        $pageCount = $this->pagination->getPageCount();
        if (isset($params[$this->pagination->pageParam])) {
            $page = (int)$params[$this->pagination->pageParam];
            if ($page < 1) {
                $page = 1;
            } elseif ($page > $pageCount) {
                $page = $pageCount;
            }
        }
        $selectOptions = [];
        for ($i = 1; $i <= $pageCount; $i++) {
            $selectOptions[$i] = $i;
        }
        $input = Select2::widget(['data' => $selectOptions, 'name' => $this->_pageInputName, 'value' => $page]);
        $input .= Html::label(
            $this->pageLabel,
            $this->_pageInputName,
            ['class' => 'active', 'style' => ['left' => '15px']]
        );
        return Html::tag('div', $input, $this->customGroupContainerOptions);
    }

    /**
     * Рендер поля ввода размера страницы
     */
    private function renderCustomPageSize(): string
    {
        $pageSize = $this->pagination->defaultPageSize;
        $params = Yii::$app->getRequest()->queryParams;
        if (isset($params[$this->pagination->pageSizeParam])) {
            $pageSize = (int)$params[$this->pagination->pageSizeParam];
            if (!$pageSize) {
                $pageSize = $this->pagination->defaultPageSize;
            }
        }
        if ($pageSize < $this->minPageSize) {
            $pageSize = $this->minPageSize;
        }
        if ($pageSize > $this->maxPageSize) {
            $pageSize = $this->maxPageSize;
        }
        $inputOptions = array_merge(
            $this->customGroupInputOptions,
            ['min' => $this->minPageSize, 'max' => $this->maxPageSize, 'step' => 1]
        );
        $input = Html::input('number', $this->_pageSizeInputName, $pageSize, $inputOptions);
        $input .= Html::label($this->pageSizeLabel, '$this->_pageSizeInputName', ['style' => ['left' => '15px']]);
        return Html::tag('div', $input, $this->customGroupContainerOptions);
    }

    /**
     * Рендер кнопки
     */
    private function renderCustomButton(): string
    {
        $buttonOptions = array_merge($this->customButtonOptions, ['id' => $this->_submitButtonId]);
        $customButtonHtml = Html::tag(
            'div',
            Html::tag('button', $this->submitButtonLabel, $buttonOptions),
            ['class' => 'input-group']
        );
        return Html::tag('div', $customButtonHtml, $this->customButtonContainerOptions);
    }

    /**
     * Регистрация JS скрипта
     */
    private function registerJs(): void
    {
        // this `pageSize = 2` is must be equal pageSize in js `urlStr.replace`
        $urlStr = $this->pagination->createUrl(0, 2);
        $pageParam = $this->pagination->pageParam;
        $pageSizeParam = $this->pagination->pageSizeParam;
        $js = <<<JS
$("#$this->_submitButtonId").on('click', function(){
  let pageInput = $('[name="$this->_pageInputName"]'),
      pageValue = pageInput.val(),
      pageSizeInput = $('[name="$this->_pageSizeInputName"]'),
      pageSizeValue = pageSizeInput.val(),
      urlStr = '$urlStr'
  urlStr = urlStr.replace('$pageParam=1', '$pageParam='+pageValue)
  window.location.href = urlStr.replace('$pageSizeParam=2', '$pageSizeParam='+pageSizeValue)
})
JS;
        $this->view->registerJs($js);
    }
}