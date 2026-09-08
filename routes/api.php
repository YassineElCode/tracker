<?php

use App\Http\Controllers\Api\V1\UserController;
use App\Http\Controllers\Autoveiligheid\AutoveiligheidController;
use App\Http\Controllers\Sbat\SbatController;
use Illuminate\Support\Facades\Route;

/* We dont want to allow any requests from other domains to our api */
Route::middleware('allow-only-local-requests')->group(function () {
    Route::get('/users/{id}', [UserController::class, 'show']);

    Route::post('/compare-datums', [AutoveiligheidController::class, 'compare']);

    Route::post('/compare-datums-sbat', [SbatController::class, 'compare']);
});
