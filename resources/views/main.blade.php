@extends('layouts/app')

@section('title')
    {{ config('app.name') }}
@endsection

@section('content')

    @component('layouts/title')
    @endcomponent

    <h1>Hello, World!</h1>


@endsection
