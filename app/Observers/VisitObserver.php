<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Visit;

class VisitObserver
{
    /**
     * Handle the Visit "saved" event. Reindex the user and space(s) in Meilisearch for ranking data.
     */
    public function saved(Visit $visit): void
    {
        $visit->user()->searchable();

        foreach ($visit->spaces()->get() as $space) {
            $space->searchable();
        }
    }
}
