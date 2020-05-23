@extends('layouts/app')

@section('title')
    {{ config('app.name') }}
@endsection

@section('content')

    @component('layouts/title')
        Dashboard
    @endcomponent

    <div class="row">
        <div class="col-sm-6 col-md-3 col-lg-4">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">
                        Welcome back!
                    </h4>
                </div>
            </div>
        </div>
    </div>


@endsection
