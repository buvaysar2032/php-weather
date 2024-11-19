<?php

namespace admin\components\widgets\detailView;

use admin\widgets\colorbox\Colorbox;
use common\components\helpers\UserUrl;
use Exception;
use Throwable;
use Yii;
use yii\bootstrap5\Html;

/**
 * ColumnImage array widget
 *
 * Колонка для вывода изображений с возможностью просмотра в отдельном модальном окне
 *
 * @package admin\components\widgets\detailView
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
     * @throws Throwable
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
        if (!Yii::$app->request->isPjax) {
            // Регистрирует ассеты для colorBox виджета, при pjax обновлении страницы не дублируем
            echo Colorbox::widget([
                'targets' => [
                    '.colorbox' => [
                        'maxWidth' => $this->maxWidth,
                        'maxHeight' => $this->maxHeight,
                    ],
                ],
                'coreStyle' => $this->coreStyle,
            ]);
        }
        return [
            'attribute' => $this->attr,
            'format' => 'html',
            'value' => function ($model) use ($valueAttr, $viewAttr) {
                $htdocs = Yii::getAlias('@root/htdocs');
                if (
                    (!$value = $this->_getRelatedClassData($model, $valueAttr))
                    || (!str_starts_with($value, 'http')
                        && (!file_exists($htdocs . urldecode($value))
                            || is_dir($htdocs . urldecode($value)))
                    )
                ) {
                    return Yii::$app->formatter->nullDisplay;
                }
                $original = UserUrl::toAbsolute($value);
                if ($this->viewAttr) {
                    $viewValue = $this->_getRelatedClassData($model, $viewAttr);
                    $preview = UserUrl::toAbsolute($viewValue);
                } else {
                    $preview = $original;
                }
                $img = Html::img($preview, ['style' => ['max-width' => '150px', 'max-height' => '50px'], 'alt' => '']);
                return Html::a(
                    $img,
                    $original,
                    ['class' => 'colorbox cboxElement', 'data' => ['toggle' => 'lightbox']],
                );
            },
        ];
    }
}
