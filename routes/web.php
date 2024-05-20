<?php

use Illuminate\Support\Facades\Route;

Route::get('/process-birds', [App\Http\Controllers\BirdController::class, 'processBirds']);
Route::get('/process-chocobos', [App\Http\Controllers\ChocoboController::class, 'processChocoboDNA']);