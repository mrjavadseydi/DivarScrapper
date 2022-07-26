<?php

use Illuminate\Support\Facades\Route;

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

Route::get('/', function () {
    return view('welcome');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard',[\App\Http\Controllers\DivarController::class,'index'])->name('dashboard');
    Route::post('/scrap',[\App\Http\Controllers\DivarController::class,'scrap'])->name('scrap');
    Route::get('/result',[\App\Http\Controllers\DivarController::class,'result'])->name('result');
    Route::get('/result/{id}',[\App\Http\Controllers\DivarController::class,'download'])->name('download');
});

require __DIR__.'/auth.php';
