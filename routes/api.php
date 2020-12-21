<?php

declare(strict_types=1);

use App\Http\Controllers\SpaceController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VisitController;
use App\Http\Controllers\VisitPunchController;
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
    Route::get('user', [UserController::class, 'showSelf'])->name('user');
    Route::put('users/{user}/spaces', [UserController::class, 'updateSpaces'])->name('users.spaces');
    Route::apiResource('users', UserController::class);

    Route::post('visits/punch', [VisitPunchController::class, 'store'])->name('visits.punch');
    Route::apiResource('visits', VisitController::class);

    Route::apiResource('spaces', SpaceController::class);
});
