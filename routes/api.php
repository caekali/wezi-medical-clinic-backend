<?php

use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('auth')->group(function () {
    Route::post("login", [AuthController::class, 'login']);
    Route::post("forgot-password", [PasswordResetLinkController::class, 'sendResetLinkEmail']);
    Route::post("reset-password", [PasswordResetLinkController::class, 'reset']);

    Route::middleware("auth:sanctum")->post("logout", [AuthController::class, 'logout']);
});

Route::apiResource('departments', DepartmentController::class);
Route::apiResource('departments.services', ServiceController::class);

Route::get('departments/trashed', [DepartmentController::class, 'trashed']);
Route::post('departments/{id}/restore', [DepartmentController::class, 'restore']);

Route::get('services/trashed', [ServiceController::class, 'trashed']);

Route::post('services/{id}/restore', [ServiceController::class, 'restore']);
Route::apiResource('appointments', AppointmentController::class);
Route::apiResource('users', UserController::class);
