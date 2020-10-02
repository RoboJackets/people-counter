<?php

namespace App\Nova\Actions;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Actions\DestructiveAction;
use Laravel\Nova\Fields\ActionFields;

class EndVisit extends DestructiveAction
{
    use InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Perform the action on the given models.
     *
     * @param  \Laravel\Nova\Fields\ActionFields  $fields
     * @param  \Illuminate\Support\Collection  $models
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        $failures = [];
        foreach ($models as $model) {
            if ($model->out_time === null) {
                $model->out_time = Carbon::now();
                $model->out_door = "admin";
                $model->save();
            } else {
                $this->markAsFailed($model, 'Already ended');
                $failures[] = $model->id;
            }
        }

        if (count($failures) > 0) {
            if (count($models) > count($failures)) {
                return Action::danger(
                    'Some selected visits have already ended: '.implode(', ', $failures)
                );
            }

            return Action::danger('All selected visits have already ended.');
        }

        return Action::message('Visit'.(1 === count($models) ? '' : 's').' marked as ended!');
    }

    /**
     * Get the fields available on the action.
     *
     * @return array
     */
    public function fields()
    {
        return [];
    }
}
