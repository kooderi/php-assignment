<?php

declare(strict_types = 1);

namespace Tests\unit\Calculator;

use ArrayObject;
use DateTime;
use PHPUnit\Framework\TestCase;
use SocialPost\Dto\SocialPostTo;
use Statistics\Dto\ParamsTo;
use Statistics\Dto\StatisticsTo;
use Statistics\Service\Factory\StatisticsServiceFactory;

/**
 * Abstract class CalculatorTestCase.
 *
 * @package Tests\unit
 */
abstract class CalculatorTestCase extends TestCase
{
    protected string $calculatorName;

    protected StatisticsTo $results;


    /**
     * Run the calculations for given time interval and save the resulting stats
     * into the `$results` property.
     */
    protected function calculateStats(DateTime $start, DateTime $end): void
    {
        // Normalize the date params to exact month start/end like in ParamsBuilder
        $start = (clone $start)->modify('first day of this month')->setTime(0, 0, 0);
        $end = (clone $end)->modify('last day of this month')->setTime(23, 59, 59);

        $params = [
            (new ParamsTo())
                ->setStatName($this->calculatorName)
                ->setStartDate($start)
                ->setEndDate($end)
        ];

        // Note: the method needs Traversable, hence regular array isn't enough
        $postData = new ArrayObject($this->getTestPosts());

        // Run the calculations and save the result for further assertions
        $statsService = StatisticsServiceFactory::create();
        $parentStatistics = $statsService->calculateStats($postData, $params);
        $this->results = $parentStatistics->getChildren()[0];
    }


    /**
     * Return a simple test dataset of SocialPost DTOs to be used across
     * different calculation tests.
     *
     * As of now the set contains 7 posts for 3 different users:
     * - Alice: 4 posts (Jan 23 * 2, Feb 23 * 2)
     * - Bob: 2 posts (Jan 23, Mar 23)
     * - John: 1 post (Jan 23)
     *
     * @TODO: e.g. some kind of fixture file could be more convenient for this?
     *
     * @return array<SocialPostTo>
     */
    protected function getTestPosts(): array
    {
        // Define the different authors here as tuples to avoid repetition
        $authors = [
            ['user001', 'Alice'],
            ['user002', 'Bob'],
            ['user055', 'John'],
        ];

        // Define data as plain array
        $posts = [
            // January 2023 (4 posts total)
            [
                'id' => 'post001',
                'date' => '2023-01-06 01:23:45',
                'author' => 0,
                'text' => 'Lorem ipsum' // 11 chars
            ],
            [
                'id' => 'post002',
                'date' => '2023-01-07 02:34:00',
                'author' => 0,
                'text' => 'Dolor sit amet' // 14 chars
            ],
            [
                'id' => 'post003',
                'date' => '2023-01-11 11:11:11',
                'author' => 1,
                'text' => 'Hello World!' // 12 chars
            ],
            [
                'id' => 'post004',
                'date' => '2023-01-31 12:12:12',
                'author' => 2,
                'text' => 'Yeehaw, this is the January record' // 34 chars
            ],
            // February 2023 (2 posts total)
            [
                'id' => 'post005',
                'date' => '2023-02-05 13:13:13',
                'author' => 0,
                'text' => 'Who reads these anyway?' // 23 chars
            ],
            [
                'id' => 'post006',
                'date' => '2023-02-12 14:14:14',
                'author' => 0,
                'text' => 'Hello world again' // 17 chars
            ],
            // March 2023 (1 post total)
            [
                'id' => 'post007',
                'date' => '2023-03-04 20:20:20',
                'author' => 1,
                'text' => 'The longest text of them all with fifty characters'
            ],
        ];

        // Convert the array items to SocialPost DTOs
        return array_map(
            function ($item) use ($authors) {
                return (new SocialPostTo())
                    ->setId($item['id'])
                    ->setDate(new DateTime($item['date']))
                    ->setAuthorId($authors[$item['author']][0])
                    ->setAuthorName($authors[$item['author']][1])
                    ->setType('status') // Hard-coded, currently not actually relevant in tests
                    ->setText($item['text']);
            },
           $posts
        );
    }
}
