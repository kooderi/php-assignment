<?php

declare(strict_types = 1);

namespace Tests\unit\Calculator;

use DateTime;
use Generator;
use Statistics\Enum\StatsEnum;

/**
 * Class MaxPostLengthTest.php
 *
 * @package Tests\unit\Calculator
 */
class MaxPostLengthTest extends CalculatorTestCase
{
    protected string $calculatorName = StatsEnum::MAX_POST_LENGTH;

    /**
     * @dataProvider maxPostLengthDataProvider
     */
    public function testAverageMonthlyPostsPerUserCalculations(
        DateTime $start,
        DateTime $end,
        float $expectedResult
    ): void
    {
        $this->calculateStats($start, $end);
        $this->assertEquals($expectedResult, $this->results->getValue());
    }

    /**
     * See `getTestPosts()` method in parent class
     */
    public function maxPostLengthDataProvider(): Generator
    {
        yield from [
            'All test posts' => [
                'start' => new DateTime('Jan 2023'),
                'end' => new DateTime('Mar 2023'),
                'result' => 50, // Longest message in whole dataset
            ],
            'January 2023' => [
                'start' => new DateTime('Jan 2023'),
                'end' => new DateTime('Jan 2023'),
                'result' => 34 // Longest text in January
            ],
            'February 2023' => [
                'start' => new DateTime('Feb 2023'),
                'end' => new DateTime('Feb 2023'),
                'result' => 23 // Longest text in February
            ],
        ];
    }
}
