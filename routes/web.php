<?php

use App\Http\Controllers\ControllerAuth;
use App\Http\Controllers\ControllerListas;
use App\Http\Controllers\ControllerNavigation;
use App\Http\Controllers\ControllerVisitantes;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/listas');

Route::middleware('guest')->group(function () {
    Route::get('/login', [ControllerNavigation::class, 'login'])->name('login');
    Route::get('/auth/google', [ControllerAuth::class, 'redirectGoogle'])->name('google');
    Route::get('/auth/google/callback', [ControllerAuth::class, 'callbackGoogle']);
});

Route::middleware('auth')->group(function () {
    Route::resource('listas', ControllerListas::class)->except('show');
    Route::post('/logout', [ControllerAuth::class, 'logout'])->name('logout');
});

// Las rutas p?blicas por token van al final para no interceptar login o listas.
Route::patch('/{token}/items/{id_item}', [ControllerVisitantes::class, 'update'])->name('visitantes.update');
Route::get('/{token}', [ControllerVisitantes::class, 'show'])->name('visitantes.show');
