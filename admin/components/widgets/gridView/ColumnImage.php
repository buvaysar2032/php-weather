<?php

namespace admin\components\widgets\gridView;

use admin\components\widgets\detailView\ColumnImage as DetailViewColumnImage;
use Exception;
use yii\helpers\Json;

/**
 * ColumnImage array widget
 *
 * Колонка для вывода изображений с возможностью просмотра в отдельном модальном окне
 *
 * @package admin\components\widgets\gridView
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
final class ColumnImage extends ColumnWidget
{
    /**
     * Имя поля со ссылкой на уменьшенную копию изображения
     */
    public ?string $viewAttr = null;

    /**
     * Максимальная ширина окна с изображением
     */
    public int $maxWidth = 800;

    /**
     * Максимальная высота окна с изображением
     */
    public int $maxHeight = 600;

    /**
     * Номер стиля Colorbox виджета
     */
    public int $coreStyle = 2;

    /**
     * {@inheritdoc}
     * @throws Exception
     */
    public function run(): array
    {
        if ($this->pjaxContainerId) {
            $this->addPjaxSuccessEvent();
        }
        return array_merge_recursive(
            DetailViewColumnImage::widget([
                'attr' => $this->attr,
                'viewAttr' => $this->viewAttr,
                'maxHeight' => $this->maxHeight,
                'maxWidth' => $this->maxWidth,
                'coreStyle' => $this->coreStyle
            ]),
            ['filter' => false]
        );
    }

    /**
     * Add pjax:success event listener for refreshing widget after pjax table update
     */
    private function addPjaxSuccessEvent(): void
    {
        $options = Json::htmlEncode(['maxWidth' => $this->maxWidth, 'maxHeight' => $this->maxHeight]);
        $script = <<<JS
$('#$this->pjaxContainerId').on('pjax:success', () => { $('.colorbox').colorbox($options) });
JS;
        $this->view->registerJs($script);
    }
}
