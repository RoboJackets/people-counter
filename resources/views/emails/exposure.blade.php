@component('mail::message')
# Hello {{ $first_name }},

You are receiving this message because you visited the Student Competition Center {{ $date }}, according to the People Counter system.
Please see below for an important message from Dr. Cunefare.

@component('mail::panel')
{{ $body }}
@endcomponent
@endcomponent
