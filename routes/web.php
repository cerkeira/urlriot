<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ScanController;

Route::get('/', [ScanController::class, 'index']);
Route::post('/check', [ScanController::class, 'check'])->name('check')
 ->middleware('throttle:4,1');
Route::get('/results/{id}', [ScanController::class, 'results'])->name('results');
Route::get('/debug-http', function () {
    return \Illuminate\Support\Facades\Http::get('https://httpbin.org/get')->body();
});