<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->group(static function (): void {
    Route::get('user', 'UserController@showSelf')->name('user');
    Route::put('users/{user}/spaces', 'UserController@updateSpaces')->name('users.spaces');
    Route::apiResource('users', 'UserController');

    Route::post('visits/punch', 'VisitPunchController@store')->name('visits.punch');
    Route::apiResource('visits', 'VisitController');

    Route::apiResource('spaces', 'SpaceController');
});
