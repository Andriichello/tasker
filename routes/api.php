<?php

use App\Http\Controllers\Api\Auth\LoginApiController;
use App\Http\Controllers\Api\Auth\MeApiController;
use App\Http\Controllers\Api\TaskApiController;
use App\Http\Controllers\Api\UserApiController;
use Illuminate\Support\Facades\Route;

/**
 * Register API routes here.
 *
 * By default, namely:
 * - path is prefixed with `/api/`
 * - name is prefixed with `.api`
 * - have `api` middleware
 */

Route::post('/login', LoginApiController::class)
    ->name('auth.login');

Route::post('/me', MeApiController::class)
    ->name('auth.me');

Route::get('/users', [UserApiController::class, 'index'])
    ->name('users.index');

Route::get('/users/{id}', [UserApiController::class, 'show'])
    ->where('id', '[1-9][0-9]*')
    ->name('users.show');

Route::get('/tasks', [TaskApiController::class, 'index'])
    ->name('tasks.index');

Route::get('/tasks/{id}', [TaskApiController::class, 'show'])
    ->where('id', '[1-9][0-9]*')
    ->name('tasks.show');

Route::post('/tasks', [TaskApiController::class, 'store'])
    ->name('tasks.store');

Route::patch('/tasks/{id}', [TaskApiController::class, 'update'])
    ->where('id', '[1-9][0-9]*')
    ->name('tasks.update');

Route::delete('/tasks/{id}', [TaskApiController::class, 'destroy'])
    ->where('id', '[1-9][0-9]*')
    ->name('tasks.destroy');
