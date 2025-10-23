<?php

use Illuminate\Support\Facades\Route;

Route::prefix('/s2200')
    ->name('s2200.')
    ->group(function () {
     
        Route::view('/form', 's2200-form')
            ->name('form');

    });

Route::prefix('/s1200')
    ->name('s1200.')
    ->group(function () {
     
        Route::view('/form', 's1200-form')
            ->name('form');

    });

