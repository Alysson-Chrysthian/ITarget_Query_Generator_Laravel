<?php

use Illuminate\Support\Facades\Route;

Route::prefix('/s2200')
    ->name('s2200.')
    ->group(function () {
     
        Route::view('/form', 's2200-form')
            ->name('form');
        Route::post('/generate-query', [App\Http\Controllers\s2200Controller::class, 'generateQuery'])
            ->name('generate-query');

    });

Route::prefix('/s1200')
    ->name('s1200.')
    ->group(function () {
     
        Route::view('/form', 's1200-form')
            ->name('form');
        Route::post('/generate-query', [App\Http\Controllers\s1200Controller::class, 'generateQuery'])
            ->name('generate-query');

    });

