<?php

use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\DoctorAvailabilityController;
use App\Http\Controllers\DoctorController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UssdController;
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
Route::get("services", [ServiceController::class, 'getAllServices']);

Route::get('departments/trashed', [DepartmentController::class, 'trashed']);
Route::post('departments/{id}/restore', [DepartmentController::class, 'restore']);

Route::get('services/trashed', [ServiceController::class, 'trashed']);

Route::post('services/{id}/restore', [ServiceController::class, 'restore']);
Route::apiResource('appointments', AppointmentController::class);
Route::apiResource('users', UserController::class);


Route::middleware('auth:sanctum')->group(function () {
    Route::get("dashboard/summary", [DashboardController::class, 'dashboard']);
});

Route::get('/departments/{id}/doctors', [DoctorController::class, 'getByDepartment']);
Route::get('/doctors/{id}/availabilities', [DoctorController::class, 'availabilities']);


Route::middleware('auth:sanctum')->group(function () {
    Route::post('/doctors/{id}/availabilities', [DoctorController::class, 'storeAvailability']);
    Route::put('/availabilities/{id}', [DoctorController::class, 'updateAvailability']);
    Route::delete('/availabilities/{id}', [DoctorController::class, 'destroyAvailability']);
});

// Handle Ussd
Route::post('/ussd', [UssdController::class, 'handle']);
