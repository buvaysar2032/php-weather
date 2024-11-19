<?php

namespace admin\widgets\apexCharts;

use common\widgets\VueWidget;
use Yii;
use yii\bootstrap5\Html;
use yii\helpers\{ArrayHelper, Json};
use yii\web\JsExpression;

/**
 * Class PieChartWidget
 *
 * @package admin\widgets\apexCharts
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
final class PieChartWidget extends VueWidget
{
    public array $labels = ['Team A', 'Team B', 'Team C'];

    public array $series = [333, 240, 333];

    public array $chartOptions = [];

    /**
     * {@inheritdoc}
     */
    public function beforeRun(): bool
    {
        if (Yii::$app->themeManager->isDark) {
            $this->chartOptions['theme']['mode'] = 'dark';
        }
        return parent::beforeRun();
    }

    /**
     * {@inheritdoc}
     */
    public function run(): string
    {
        $options = [
            'type' => 'pie',
            'width' => 380,
            ':series' => Json::htmlEncode($this->series),
            ':options' => Json::htmlEncode(
                ArrayHelper::merge(
                    [
                        'chart' => [
                            'width' => 380,
                            'type' => 'pie',
                            'toolbar' => ['show' => true, 'export' => ['csv' => ['columnDelimiter' => ';']]]
                        ],
                        'labels' => $this->labels,
                        'legend' => [
                            'formatter' => new JsExpression(
                                'function(seriesName, opts) { return [seriesName, " - ", ((opts.w.globals.series[opts.seriesIndex] / opts.w.globals.series.reduce((a, b) => a + b, 0)) * 100).toFixed(1) + "%"]; }'
                            )
                        ],
                        'responsive' => [
                            [
                                'breakpoint' => 1280,
                                'options' => [
                                    'chart' => ['width' => 300],
                                    'legend' => ['position' => 'bottom']
                                ]
                            ]
                        ],
                    ],
                    $this->chartOptions
                )
            ),
        ];
        return Html::tag('apex-charts', '', $options);
    }
}