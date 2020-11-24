@component('mail::message')
# Good Morning!

There @choice('is|are', count($space->activeVisits)) currently {{ count($space->activeVisits) }} active @choice('visit|visits', count($space->activeVisits)) for the {{ $space->name }} space.

@component('mail::panel')
## Active Visits for {{ $space->name }}
@if(count($space->activeVisits) > 0)
@foreach($space->activeVisits as $visit)
- **{{ $visit->user->first_name }} {{ $visit->user->last_name }}** (since {{ $visit->in_time }})
@endforeach
@else
- No active visits at this time
@endif
@endcomponent

Please remind your members to be diligent about signing in and out of spaces using the kiosks for each visit.

Self-service remote sign-out is available at {{ url('/') }}.

Thanks,
SCC Governing Board
@endcomponent
