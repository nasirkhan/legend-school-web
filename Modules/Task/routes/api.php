<?php

use Illuminate\Support\Facades\Route;
use Modules\Task\Http\Controllers\Api\TaskController;

Route::prefix('v1')
    ->middleware('auth:sanctum')
    ->name('api.v1.tasks.')
    ->group(function () {
        Route::get('/tasks', [TaskController::class, 'index'])->name('index');
        Route::post('/tasks', [TaskController::class, 'store'])->name('store');
        Route::get('/tasks/{task}', [TaskController::class, 'show'])->name('show');
        Route::match(['put', 'patch'], '/tasks/{task}', [TaskController::class, 'update'])->name('update');
        Route::delete('/tasks/{task}', [TaskController::class, 'destroy'])->name('destroy');
        Route::patch('/tasks/{task}/complete', [TaskController::class, 'complete'])->name('complete');
        Route::patch('/tasks/{task}/reopen', [TaskController::class, 'reopen'])->name('reopen');
    });
