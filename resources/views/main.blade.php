@extends('layouts/app')

@section('title')
    {{ config('app.name') }}
@endsection

@section('content')

    @component('layouts/title')
    @endcomponent

	<dashboard max-people="{{config('app.maxpeople')}}"/>


@endsection
