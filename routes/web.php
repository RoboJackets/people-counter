<?php

declare(strict_types=1);

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Route;
use Illuminate\View\View;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::middleware('auth.cas.force')->group(static function (): void {
    Route::get('/', static function (): View {
        return view('main');
    });
});

Route::get('kiosk', static function (): View {
    return view('kiosk');
})->name('kiosk');

Route::get('privacy', static function (): RedirectResponse {
    return redirect('https://www.gatech.edu/privacy');
});

Route::get('nova/logout', static function (): RedirectResponse {
    return redirect('logout');
})->name('nova.logout');

Route::get('login', static function (): RedirectResponse {
    return redirect()->intended();
})->name('login')->middleware('auth.cas.force');

Route::get('logout', static function (): void {
    \Illuminate\Support\Facades\Session::flush();
    cas()->logout(config('app.url'));
})->name('logout');
