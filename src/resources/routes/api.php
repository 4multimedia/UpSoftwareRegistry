<?php

use Illuminate\Support\Facades\Route;

Route::prefix('pl')->as('pl.')->middleware(['restricted-ip'])->namespace('Poland')->group(function() {
    Route::post('/gus', 'GusController')->name('gus');
});
