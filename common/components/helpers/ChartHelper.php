<?php

namespace common\components\helpers;

use admin\widgets\apexCharts\Line;
use Generator;
use Yii;
use yii\base\InvalidConfigException;

class ChartHelper
{
    /**
     * @return Generator<int>
     *
     * @throws InvalidConfigException
     */
    public static function periodIterator(
        int $startTime,
        int $endTime,
        int|string $step = '+1 day',
        bool|int $cache = true
    ): Generator {
        $startTime = (int)strtotime('today', $startTime);
        $endTime = (int)strtotime('today', $endTime);
        $currentTime = $startTime;
        $stepSeconds = abs(strtotime($step) - time());
        while ($currentTime < $endTime) {
            $date = Yii::$app->formatter->asDate($currentTime, Line::DATE_FORMAT);
            $currentEndTime = self::increaseTime($currentTime, $step);
            if ($cache !== true) {
                $cache = (time() - $currentTime) > $stepSeconds ? 0 : $cache;
            }
            yield $date => [$currentTime, $currentEndTime, $cache];
            $currentTime = $currentEndTime + 1;
        }
    }

    /**
     * @throws InvalidConfigException
     */
    private static function increaseTime(int $time, int|string $step): int
    {
        if (is_string($step)) {
            $res = strtotime($step, $time);
            if ($res === false) {
                throw new InvalidConfigException(
                    'Invalid `step` argument, string type must be compatible with `strtotime` function'
                );
            }
            return $res - 1;
        }
        return $time + $step;
    }
}
