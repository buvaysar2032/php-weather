<?php

namespace admin\components\widgets\detailView;

use admin\modules\rbac\components\RbacHtml;
use common\enums\DictionaryInterface;
use yii\base\InvalidConfigException;

/**
 * Column array widget
 *
 * Колонка для вывода обычных данных
 *
 * @package admin\components\widgets\detailView
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
final class Column extends ColumnWidget
{
    /**
     * {@inheritdoc}
     */
    public string $attr = 'id';

    /**
     * Название атрибута из которого брать данные для отображения
     */
    public ?string $viewAttr = null;

    /**
     * Формат отображения данных
     *
     * @see \common\components\UserFormatter
     */
    public string $format = 'raw';

    /**
     * Массив возможных значений
     */
    public string|array|DictionaryInterface $items = [];

    /**
     * Путь до контроллера на внешнюю модель данных
     *
     * Например, если передать `user` то будет ссылка формата `user/view?id=`
     */
    public ?string $pathLink = null;

    /**
     * {@inheritdoc}
     * @throws InvalidConfigException
     */
    public function init(): void
    {
        if (is_string($this->items) && enum_exists($this->items)) {
            $this->items = $this->items::indexedDescriptions(true);
        }
        parent::init();
    }

    /**
     * {@inheritdoc}
     */
    public function run(): array
    {
        $vars = self::_parseAttrValue($this->attr, $this->viewAttr);
        /**
         * Extracted variables
         *
         * @var string $valueAttr
         * @var bool   $isRelative
         * @var string $snakeAttr
         * @var string $viewAttr
         */
        extract($vars);
        $vueComponent = null;
        if ($this->format === 'markdown') {
            $vueComponent = $this->format;
            $this->format = 'raw';
        }
        return [
            'attribute' => $this->attr,
            'format' => $this->format,
            'value' => function ($model) use ($valueAttr, $viewAttr, $vueComponent) {
                $data = self::_getRelatedClassData($model, $valueAttr);
                $text = self::_getRelatedClassData($model, $viewAttr);

                if ($vueComponent === 'markdown') {
                    // Markdown форматирование
                    $source = $text ?? $data;
                    $result = <<<HTML
<vue-simple-markdown source="$source"></vue-simple-markdown>
HTML;
                } elseif ($this->pathLink) {
                    // Если это ссылка на другой раздел
                    $result = RbacHtml::a(
                        $text,
                        ["/$this->pathLink/view", 'id' => $data],
                        ['style' => ['font-weight' => 'bold'], 'target' => '_blank']
                    );
                } elseif (count($this->items)) {
                    // Вывод элемента списка
                    $result = $this->items[$data] ?? null;
                } else {
                    // Вывод фактического значения
                    $result = $text ?? $data;
                }
                return $result;
            },
        ];
    }
}
