<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\EmployeeController;
use App\Http\Controllers\Api\PublicEmployeeController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth:sanctum');

Route::get('/public/employees', [PublicEmployeeController::class, 'index']);

Route::get('/public/employees/{id}', [PublicEmployeeController::class, 'show'])
    ->whereNumber('id');

Route::get('/user', [AuthController::class, 'me'])->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/employees', [EmployeeController::class, 'index']);

    Route::post('/employees', [EmployeeController::class, 'store']);

    Route::get('/employees/{employee}', [EmployeeController::class, 'show'])
        ->whereNumber('employee');

    Route::match(['put', 'patch'], '/employees/{employee}', [EmployeeController::class, 'update'])
        ->whereNumber('employee');

    Route::delete('/employees/{employee}', [EmployeeController::class, 'destroy'])
        ->whereNumber('employee');
});
