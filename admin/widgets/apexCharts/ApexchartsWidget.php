<?php

namespace admin\widgets\apexCharts;

use common\widgets\VueWidget;
use Yii;
use yii\base\InvalidConfigException;
use yii\bootstrap5\Html;
use yii\helpers\{ArrayHelper, Json};

/**
 * Class ApexchartsWidget
 *
 * @package admin\widgets\apexCharts
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
final class ApexchartsWidget extends VueWidget
{
    /**
     * Опции js компонента apexcharts, по умолчанию конфиг на даты вдоль оси X
     *
     * @see https://apexcharts.com/docs/creating-first-javascript-chart/
     */
    public array $chartOptions = [
        'chart' => [
            'toolbar' => [
                'show' => true,
                'autoSelected' => 'zoom',
                'export' => ['csv' => ['headerCategory' => 'Дата']]
            ]
        ],
        'xaxis' => ['type' => 'datetime'],
        'tooltip' => ['x' => ['format' => 'd.M.yyyy']],
        'colors' => ['#2E93fA', '#488932'],
        'plotOptions' => [
            'bar' => [
                'horizontal' => false,
                'endingShape' => 'rounded'
            ]
        ],
        'dataLabels' => ['enabled' => false],
        'stroke' => [
            'show' => true,
            'width' => 2,
            'curve' => 'smooth'
        ],
        'legend' => [
            'verticalAlign' => 'bottom',
            'horizontalAlign' => 'left'
        ]
    ];

    /**
     * Тип графика
     */
    public string $type = 'line';

    /**
     * CSS ширина компонента
     */
    public string $width = '100%';

    /**
     * CSS высота компонента
     */
    public int $height = 350;

    /**
     * Линии на графике
     */
    private array $series = [];

    /**
     * Массив объектов линий, которые необходимы для "подгонки" масштабов линий относительно друг друга
     */
    private array $lines = [];

    /**
     * {@inheritdoc}
     * @throws InvalidConfigException
     */
    public function beforeRun(): bool
    {
        Line::adjustLinesLength(...$this->lines);
        $linesCount = count($this->lines);
        if (!$linesCount) {
            throw new InvalidConfigException('None lines on the chart');
        }
        if ($linesCount >= 1) {
            $this->chartOptions = ArrayHelper::merge($this->chartOptions, [
                'xaxis' => [
                    'categories' => ArrayHelper::getColumn($this->lines[0]->sortedData, 'x')
                ]
            ]);
        }
        foreach ($this->lines as $line) {
            $this->series[] = [
                'name' => $line->name,
                'data' => ($linesCount >= 1) ? ArrayHelper::getColumn($line->sortedData, 'y') : $line->sortedData
            ];
        }
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
            ':options' => Json::htmlEncode($this->chartOptions),
            ':series' => Json::htmlEncode($this->series),
            'type' => $this->type,
            'width' => $this->width,
            'height' => $this->height
        ];
        return Html::tag('apexchart', '', $options);
    }

    /**
     * Добавить линию на график
     */
    public function addLine(Line $line): void
    {
        $this->lines[] = $line;
    }
}