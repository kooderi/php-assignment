<?php

namespace Statistics\Calculator;

use DateTime;
use SocialPost\Dto\SocialPostTo;
use Statistics\Dto\StatisticsTo;

/**
 * Class AveragePostsPerUserPerMonth
 *
 * @package Statistics\Calculator
 */
class AveragePostsPerUserPerMonth extends AbstractCalculator
{

    protected const UNITS = 'posts per user';

    /**
     * @var array<string, int>
     */
    private array $totals = [];

    /**
     * @var array<string, array<string>>
     */
    private array $uniqueUsers = [];

     protected function doAccumulate(SocialPostTo $postTo): void
    {
        $month = $postTo->getDate()?->format('Y-m') ?? 'unknown';
        $userId = $postTo->getAuthorId();

        // Collect unique posters for each months
        if (!isset($this->uniqueUsers[$month])) {
            $this->uniqueUsers[$month] = [];
        }

        if (! in_array($userId, $this->uniqueUsers[$month])) {
            $this->uniqueUsers[$month][] = $userId;
        }

        // Also count total number of posts for each month
        $this->totals[$month] = ($this->totals[$month] ?? 0) + 1;
    }

      protected function doCalculate(): StatisticsTo
    {
        $stats = new StatisticsTo();

        // Ensure that the stats come in correct chronological order
        ksort($this->totals);

        foreach ($this->totals as $monthId => $totalPosts) {
            // Round to a reasonable amount of decimals
            $average = round(
                $totalPosts / count($this->uniqueUsers[$monthId]),
                    2
                );

            $monthLabel = (new DateTime($monthId))->format('F Y');

            $child = (new StatisticsTo())
                ->setName($this->parameters->getStatName())
                ->setSplitPeriod($monthLabel)
                ->setValue($average)
                ->setUnits(self::UNITS);

            $stats->addChild($child);
        }

        return $stats;
    }
}
