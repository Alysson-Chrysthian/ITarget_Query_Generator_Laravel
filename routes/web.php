<?php

use Illuminate\Support\Facades\Route;

Route::post('/s2200/generate-query', [App\Http\Controllers\s2200Controller::class, 'generateQuery'])
    ->name('s2200.generate-query');

Route::post('/s1200/generate-query', [App\Http\Controllers\s1200Controller::class, 'generateQuery'])
    ->name('s1200.generate-query');

Route::post('/s1210/generate-query', [App\Http\Controllers\s1210Controller::class, 'generateQuery'])
    ->name('s1210.generate-query');

Route::post('/generate-query', [App\Http\Controllers\GenerateQueryController::class, 'generateQuery'])
    ->name('generate-query');
Route::view('/generate-query', 'generate-query-form')
    ->name('generate-query');