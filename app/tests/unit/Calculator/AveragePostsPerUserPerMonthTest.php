<?php

declare(strict_types = 1);

namespace Tests\unit\Calculator;

use DateTime;
use Generator;
use Statistics\Enum\StatsEnum;

/**
 * Class AveragePostsPerUserPerMonthTest
 *
 * @package Tests\unit\Calculator
 */
class AveragePostsPerUserPerMonthTest extends CalculatorTestCase
{
    protected string $calculatorName = StatsEnum::AVERAGE_POSTS_NUMBER_PER_USER_PER_MONTH;

    /**
     * @dataProvider averagePostsPerUserPerMonthDataProvider
     */
    public function testAverageMonthlyPostsPerUserCalculations(
        DateTime $start,
        DateTime $end,
        int $expectedCount,
        array $exptectedResults
    ): void
    {
        $this->calculateStats($start, $end);

        $children = $this->results->getChildren();

        // Check that we got correct amount of results
        $this->assertCount($expectedCount, $children);

        // Also check that results are as expected
        foreach ($exptectedResults as $i => [$name, $averagePosts]) {
            $this->assertEquals($name, $children[$i]->getSplitPeriod());
            $this->assertEquals($averagePosts, $children[$i]->getValue());
            $this->assertEquals('posts per user', $children[$i]->getUnits());

            next($children);
        }
    }

    /**
     * See `getTestPosts()` method in parent class
     */
    public function averagePostsPerUserPerMonthDataProvider(): Generator
    {
        yield from [
            'Whole test dataset (3 months)' => [
                'start' => new DateTime('Jan 2023'),
                'end' => new DateTime('Mar 2023'),
                'result_count' => 3,
                'results' => [ // [month name, average posts]
                    ['January 2023',  1.33], // 4 posts, 3 users
                    ['February 2023', 2], // 2 posts, 1 user
                    ['March 2023', 1] // 1 post, 1 user
                ]
            ],
            'Smaller timespan (Jan-Feb 2023)' => [
                'start' => new DateTime('Jan 2023'),
                'end' => new DateTime('Feb 2023'),
                'result_count' => 2,
                'results' => [
                    ['January 2023',  1.33],
                    ['February 2023', 2],
                ]
            ],
            'Just one month (Feb 2023)' => [
                'start' => new DateTime('Feb 2023'),
                'end' => new DateTime('Feb 2023'),
                'result_count' => 1,
                'results' => [
                    ['February 2023', 2]
                ]
            ],
            'Date interval without overlap to test data' => [
                'start' => new DateTime('Jan 2020'),
                'end' => new DateTime('Dec 2020'),
                'result_count' => 0,
                'results' => []
            ],
        ];
    }
}
