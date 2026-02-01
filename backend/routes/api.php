<?php

use App\Http\Controllers\Api\HeadlineController;
use App\Http\Controllers\Api\ThemeController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group.
|
*/

Route::prefix('v1')->group(function () {
    Route::get('/headlines', [HeadlineController::class, 'index']);
    Route::get('/themes', [ThemeController::class, 'index']);
});
