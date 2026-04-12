<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->name('api.v1.')->group(function () {
    Route::post('/login', [AuthController::class, 'login'])
        ->middleware(['guest', 'throttle:5,1'])
        ->name('login');

    Route::middleware('auth:sanctum')->group(function () {
        Route::delete('/logout', [AuthController::class, 'logout'])->name('logout');

        Route::prefix('users')
            ->name('users.')
            ->controller(UserController::class)
            ->group(function () {
                Route::get('/profile/{username?}', 'show')->name('profile');
                Route::match(['put', 'patch'], '/profile', 'update')->name('updateProfile');
            });
    });
});
