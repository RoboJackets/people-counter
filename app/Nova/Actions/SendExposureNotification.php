<?php

declare(strict_types=1);

namespace App\Nova\Actions;

use App\Models\User;
use App\Notifications\ExposureNotification;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Date;
use Laravel\Nova\Fields\Heading;
use Laravel\Nova\Fields\Textarea;

class SendExposureNotification extends Action
{
    /**
     * Disables action log events for this action.
     *
     * @var bool
     */
    public $withoutActionEvents = true;

    /**
     * Perform the action on the given models.
     *
     * @param  \Laravel\Nova\Fields\ActionFields  $fields
     * @param  \Illuminate\Support\Collection  $models
     *
     * @return array<string,string>
     */
    public function handle(ActionFields $fields, Collection $models): array
    {
        $start_date = Carbon::create($fields->start_date)->startOfDay();
        $start_date_string = $start_date->isoFormat('dddd MMMM Do, Y');
        $end_date = Carbon::create($fields->end_date)->endOfDay();
        $end_date_string = $end_date->isoFormat('dddd MMMM Do, Y');
        $same_day = $start_date->isSameDay($end_date);
        $date_string = ($same_day) ? 'on '.$start_date_string : 'between '.$start_date_string.' and '.$end_date_string;

        // Fetch message recipients
        $recipients = User::whereHas('visits', static function (Builder $query) use ($start_date, $end_date) {
            $query->whereBetween('in_time', [$start_date, $end_date]);
            $query->orWhereBetween('out_time', [$start_date, $end_date]);
        })->get();
        if (count($recipients) === 0) {
            return Action::danger('No visits found matching criteria.');
        }

        foreach ($recipients as $recipient) {
            $recipient->notify(new ExposureNotification($date_string, $fields->message));
            Log::info(self::class.': Successfully queued exposure notification for '.$recipient->username);
        }

        return Action::message('Notifications successfully queued!');
    }

    /**
     * Get the fields available on the action.
     */
    public function fields(): array
    {
        $msg = "A message will be sent to each user with a visit with an in or out time between 12:00am on start date".
            " and 11:59pm on end date";
        return [
            Heading::make($msg),
            Date::make('Start Date')->rules('required'),
            Date::make('End Date')->rules('required'),
            Textarea::make('Message')->rules('required')
                ->help(
                    'Message to be included in the email after the standard introduction'
                ),
        ];
    }
}
