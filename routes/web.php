<?php

use App\Http\Controllers\Web\WebController;
use Illuminate\Support\Facades\Route;

Route::get('/', [WebController::class, 'view'])
    ->name('landing');

Route::get('/login', [WebController::class, 'view'])
    ->name('login');

// Task routes - all handled by the same view for SPA
Route::get('/create', [WebController::class, 'view'])
    ->name('task.create');

Route::get('/{id}', [WebController::class, 'view'])
    ->name('task.show')
    ->where('id', '[0-9]+');

Route::get('/{id}/edit', [WebController::class, 'view'])
    ->name('task.edit')
    ->where('id', '[0-9]+');
