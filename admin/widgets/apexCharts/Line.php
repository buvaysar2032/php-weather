<?php

namespace admin\widgets\apexCharts;

use Yii;
use yii\base\{Component, InvalidConfigException};
use yii\helpers\ArrayHelper;

/**
 * Class Line
 *
 * @package admin\widgets\apexCharts
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 *
 * @property-read array $data
 * @property-read array $sortedData
 * @property-read string $subAxis
 */
final class Line extends Component
{
    /**
     * Формат выводимой даты
     */
    public const DATE_FORMAT = 'php:Y-m-d';

    /**
     * Тип графика с привязкой к датам
     */
    public const TYPE_DATE = 'date';

    /**
     * Тип простого линейного графика
     */
    public const TYPE_LINEAR = 'line';

    /**
     * Текущий тип
     *
     * По умолчанию TYPE_DATE
     */
    public string $type = self::TYPE_DATE;

    /**
     * Название графика
     *
     * Обязательное поле
     */
    public string $name;

    /**
     * Ось для сортировки точек
     */
    public string $sortAxis = 'x';

    /**
     * Шаг главной (временной) оси для добавления точек
     */
    public string|int $mainAxisDelta = '+1 day';

    /**
     * Шаг второй оси (данные) для увеличения значения точек
     */
    public int|float $subAxisDelta = 1;

    /**
     * Направление сортировки точек
     */
    public int $sortDirection = SORT_ASC;

    /**
     * Значение точки, когда она "пустая"
     */
    public string|int|null $emptyValue = 0;

    /**
     * Заполнение пустот в линии "пустыми" точками
     */
    public bool $fillSpaces = false;

    /**
     * Внутренний массив данных с текущими точками
     */
    private array $_data = [];

    /**
     * {@inheritdoc }
     * @throws InvalidConfigException
     */
    public function init(): void
    {
        if (!isset($this->name)) {
            throw new InvalidConfigException(self::class . '::name property must be set');
        }
        parent::init();
    }

    /**
     * Получить название второй оси
     */
    public function getSubAxis(): string
    {
        return $this->sortAxis === 'x' ? 'y' : 'x';
    }

    /**
     * Получить сырой список точек
     */
    public function getData(): array
    {
        return $this->_data;
    }

    /**
     * Отсортированный, нормализованный список точек для ровного вывода в графике
     *
     * @throws InvalidConfigException
     */
    public function getSortedData(): array
    {
        if ($this->fillSpaces) {
            ArrayHelper::multisort($this->_data, $this->sortAxis, $this->sortDirection);
            $this->_fillEmpty();
        }
        ArrayHelper::multisort($this->_data, $this->sortAxis, $this->sortDirection);
        return $this->_data;
    }

    /**
     * Добавить точку в график
     *
     * @throws InvalidConfigException
     */
    public function addPoint(int|string $y, int|string $x = null): void
    {
        if ($x === null) {
            $x = $this->sortedData[count($this->sortedData) - 1][$this->sortAxis] ?? null;
            switch ($this->type) {
                case self::TYPE_DATE:
                    if (!$x) {
                        $x = Yii::$app->formatter->asDate(time(), self::DATE_FORMAT);
                    } else {
                        $x = Yii::$app->formatter->asDate(
                            strtotime($this->mainAxisDelta, strtotime($x)),
                            self::DATE_FORMAT
                        );
                    }
                    break;
                case self::TYPE_LINEAR:
                    $x += $this->mainAxisDelta;
                    break;
                default:
                    break;
            }
        }
        $this->_data[] = [$this->sortAxis => $x, $this->subAxis => $y];
    }

    /**
     * Увеличить значение в точке
     *
     * @throws InvalidConfigException
     */
    public function incrementPoint(float|int $x): void
    {
        foreach ($this->_data as &$item) {
            if ((float)$item[$this->sortAxis] === $x) {
                $item[$this->subAxis] += $this->subAxisDelta;
                return;
            }
        }
        unset($item);
        $this->addPoint((float)$this->subAxisDelta, $x);
    }

    /**
     * Уменьшить значение в точке
     *
     * @throws InvalidConfigException
     */
    public function decreasePoint(float|int $x): void
    {
        foreach ($this->_data as &$item) {
            if ((float)$item[$this->sortAxis] === $x) {
                $item[$this->subAxis] -= $this->subAxisDelta;
                return;
            }
        }
        unset($item);
        if ($this->emptyValue !== null) {
            $this->addPoint($this->emptyValue, $x);
        }
    }

    /**
     * Заполнение пропусков в графике
     *
     * @throws InvalidConfigException
     */
    private function _fillEmpty(): void
    {
        if ($this->_data) {
            switch ($this->type) {
                case self::TYPE_DATE:
                    $this->_fillEmptyDateFormat();
                    break;
                case self::TYPE_LINEAR:
                    $this->_fillEmptyLinearFormat();
                    break;
            }
        }
    }

    /**
     * Заполнение пропусков в графике с датами
     *
     * @throws InvalidConfigException
     */
    private function _fillEmptyDateFormat(): void
    {
        $endTimestamp = strtotime(end($this->_data)[$this->sortAxis]);
        for (
            $date = $this->_data[0][$this->sortAxis];
            strtotime($date) < $endTimestamp;
            $date = Yii::$app->formatter->asDate(strtotime($this->mainAxisDelta, strtotime($date)), self::DATE_FORMAT)
        ) {
            foreach ($this->_data as $item) {
                if ($item[$this->sortAxis] === $date) {
                    continue 2;
                }
            }
            $this->addPoint($this->emptyValue, $date);
        }
    }

    /**
     * Заполнение пропусков в линейном графике
     *
     * @throws InvalidConfigException
     */
    private function _fillEmptyLinearFormat(): void
    {
        $endOfAxis = end($this->_data)[$this->sortAxis];
        for (
            $currentAxisValue = $this->_data[0][$this->sortAxis];
            $currentAxisValue < $endOfAxis;
            $currentAxisValue += $this->mainAxisDelta
        ) {
            foreach ($this->_data as $item) {
                if ((float)$item[$this->sortAxis] === (float)$currentAxisValue) {
                    continue 2;
                }
            }
            $this->addPoint($this->emptyValue, $currentAxisValue);
        }
    }

    /**
     * Выровнять длину линий между друг другом
     *
     * Заполнит пропущенные друг у друга точки нулевыми значениями
     *
     * @param Line ...$lines Линии которые необходимо выровнять
     */
    public static function adjustLinesLength(self ...$lines): void
    {
        foreach ($lines as $lineKey => $line) {
            foreach ($lines as $checkLineKey => $checkLine) {
                if ($lineKey !== $checkLineKey) {
                    $line->fillSpaces($checkLine);
                }
            }
        }
    }

    /**
     * Заполнение пропусков на основе данных из другой линии
     *
     * @param Line $line Другая линия
     */
    private function fillSpaces(self $line): void
    {
        if ($this->emptyValue !== null) {
            foreach ($line->data as $key => $linePoint) {
                foreach ($this->_data as $selfPoint) {
                    // Если точки совпали, то пропускаем
                    if ($linePoint[$this->sortAxis] === $selfPoint[$this->sortAxis]) {
                        continue 2;
                    }
                }
                array_splice(
                    $this->_data,
                    $key,
                    0,
                    [[$this->sortAxis => $linePoint[$this->sortAxis], $this->subAxis => $this->emptyValue]]
                );
            }
        }
    }
}