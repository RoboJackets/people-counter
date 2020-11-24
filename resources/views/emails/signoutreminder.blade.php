@component('mail::message')
# Hello!

We noticed that you've been signed in to the SCC for a long time.
If you're not in the SCC, please go to [{{ url('/') }}]({{ url('/') }}) and click "End Visit" to sign out.
If you are still there, please don't forget to sign out when you leave.

@component('mail::button', ['url' => url('/')])
End Visit
@endcomponent

Thanks,
SCC Governing Board
@endcomponent
