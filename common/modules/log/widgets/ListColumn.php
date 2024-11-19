<?php

namespace common\modules\log\widgets;

use admin\components\widgets\gridView\ColumnWidget;
use kartik\grid\DataColumn;
use Yii;
use yii\bootstrap5\Html;
use yii\helpers\Json;

/**
 * Class GridViewListColumn
 *
 * @package log
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
class ListColumn extends ColumnWidget
{
    /**
     * {@inheritdoc}
     */
    public function run(): array
    {
        $vars = self::_parseAttrValue($this->attr);
        /**
         * Extracted variables
         *
         * @var string $valueAttr
         * @var bool   $isRelative
         * @var string $snakeAttr
         */
        extract($vars);

        $contentOptions = ['style' => ['white-space' => 'nowrap']];
        $headerOptions = [];
        if (isset($this->width)) {
            Html::addCssStyle($contentOptions, [
                'white-space' => 'break-spaces',
                'max-width' => $this->width,
                'overflow' => 'auto'
            ]);
            Html::addCssStyle($headerOptions, ['width' => $this->width]);
        }
        return [
            'class' => DataColumn::class,
            'attribute' => $this->attr,
            'filterInputOptions' => [
                'class' => 'form-control',
                'placeholder' => Yii::t('app', 'Search')
            ],
            'format' => 'html',
            'contentOptions' => $contentOptions,
            'headerOptions' => $headerOptions,
            'value' => function ($data) use ($valueAttr) {
                $value = htmlspecialchars_decode($this->_getRelatedClassData($data, $valueAttr));
                $fields = Json::decode($value);
                if (is_array($fields)) {
                    $list = Html::ul($fields, ['style' => 'white-space: pre-wrap']);
                } else {
                    $list = $fields;
                }
                return $list;
            },
        ];
    }
}
