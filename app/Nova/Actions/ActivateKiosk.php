<?php

namespace App\Nova\Actions;

use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Heading;
use Laravel\Nova\Fields\Text;

class ActivateKiosk extends Action
{
    /**
     * Perform the action on the given models.
     *
     * @param  \Laravel\Nova\Fields\ActionFields  $fields
     * @param  \Illuminate\Support\Collection  $models
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        $params = [
            'door' => $fields->door,
            'space' => $models->first()->id
        ];

        $url = route('kiosk');
        $query = http_build_query($params);
        return Action::redirect($url.'?'.$query);
    }

    /**
     * Get the fields available on the action.
     *
     * @return array
     */
    public function fields()
    {
        return [
            Heading::make('At what door is this kiosk located?'),
            Text::make('Door'),
            Heading::make('Upon activation, you will be redirected to the kiosk.')
        ];
    }
}
