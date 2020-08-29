<?php

declare(strict_types=1);

namespace App\Nova\Metrics;

use App\Space;
use Laravel\Nova\Metrics\Partition;
use Laravel\Nova\Metrics\PartitionResult;

class VisitsBySpace extends Partition
{
    /**
     * The displayable name of the metric.
     *
     * @var string
     */
    public $name = 'Visits by Space';

    /**
     * Calculate the value of the metric.
     */
    public function calculate(): PartitionResult
    {
        $spaces = Space::withCount('visits')->get();
        $result = [];
        foreach ($spaces as $space) {
            $result = array_merge($result, [$space->name => $space->visits_count]);
        }
        return $this->result($result);
    }

    /**
     * Get the URI key for the metric.
     */
    public function uriKey(): string
    {
        return 'visits-by-space';
    }
}
