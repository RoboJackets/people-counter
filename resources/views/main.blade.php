@extends('layouts/app')

@section('title')
    {{ config('app.name') }}
@endsection

@section('content')

    @component('layouts/title')
        {{ config('app.name') }}
    @endcomponent

    <home-page></home-page>

@endsection
