<?php

namespace admin\tests\unit\widgets\apexCharts;

use admin\widgets\apexCharts\Line;
use Codeception\Test\Unit;
use Exception;

/**
 * Class LineTest
 * @package admin\tests\unit\widgets\apexCharts
 * @author m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
class LineTest extends Unit
{
    /**
     * @throws \yii\base\InvalidConfigException
     * @throws Exception
     */
    public function testAdjust(): void
    {
        /** @var \admin\widgets\apexCharts\Line[] $lines */
        $lines = [];
        $maxLength = 0;
        for ($i = 0; $i < 5; $i++) {
            $line = new Line(['name' => 'Test ' . $i, 'fillSpaces' => true]);
            $length = random_int(50, 70);
            if ($maxLength < $length) {
                $maxLength = $length;
            }
            for ($index = 0; $index < $length; $index++) {
                $line->addPoint(random_int(10, 40));
            }
            $lines[] = $line;
        }
        Line::adjustLinesLength(...$lines);
        foreach ($lines as $line) {
            expect(count($line->sortedData))->toBe($maxLength);
        }
    }
}