<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ControllerVisitantes;




//Siempre va al final de la ruta
Route::patch('/{token}/items/{id_item}', [ControllerVisitantes::class, 'update'])->name('visitantes.update');
Route::get('/{token}', [ControllerVisitantes::class, 'show'])->name('visitantes.show');
