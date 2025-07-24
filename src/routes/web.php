<?php

use Illuminate\Support\Facades\Route;
use wimbo\Onlydev\Http\Controllers\OnlydevController;

Route::middleware(['web'])
    ->prefix('onlydev')
    ->group(function () {
        Route::get('/change-user/{user}', [OnlydevController::class, 'onlydevChangeUser'])
            ->name('onlydev.changeUser');
    });
