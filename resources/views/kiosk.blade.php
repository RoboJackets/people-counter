@extends('layouts/app')

@section('title')
    {{ config('app.name') }}
@endsection

@section('content')

    @component('layouts/title')
    @endcomponent

    <kiosk max-people="{{config('app.maxpeople')}}">

@endsection
