<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\FcmTokenController;
use App\Http\Controllers\Api\ParentController;
use App\Http\Controllers\Api\PasswordController;
use App\Http\Controllers\Api\StudentDataController;
use App\Http\Middleware\VerifyParentOwnership;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// ======================================================
// PUBLIC
// ======================================================

Route::post(
    '/login',
    [AuthController::class, 'login']
)->name('api.login');

// ======================================================
// AUTHENTIFIÉ (tout rôle)
// ======================================================

Route::middleware('auth:sanctum')->group(function () {

    Route::post(
        '/logout',
        [AuthController::class, 'logout']
    )->name('api.logout');

    Route::get(
        '/user',
        fn (Request $request) => $request->user()
    )->name('api.user');

    Route::get(
        '/profile',
        [ParentController::class, 'profile']
    )->name('api.profile');

    Route::get(
        '/announcements',
        [ParentController::class, 'announcements']
    )->name('api.announcements');

    Route::put(
        '/user/password',
        [PasswordController::class, 'update']
    )->name('api.password.update');

    // ======================================================
    // FCM TOKEN
    // ======================================================

    Route::post(
        '/fcm-token',
        [FcmTokenController::class, 'store']
    )->name('api.fcm-token.store');

    Route::delete(
        '/fcm-token',
        [FcmTokenController::class, 'destroy']
    )->name('api.fcm-token.destroy');

    // ======================================================
    // DONNÉES ÉLÈVE (résolu via students.user_id)
    // ======================================================

    Route::middleware(VerifyParentOwnership::class)
        ->prefix('student')
        ->group(function () {

            Route::get(
                '/',
                [StudentDataController::class, 'show']
            )->name('api.student.show');

            Route::get(
                '/enrollments',
                [StudentDataController::class, 'enrollments']
            )->name('api.student.enrollments');

            Route::get(
                '/payments',
                [StudentDataController::class, 'payments']
            )->name('api.student.payments');

            Route::get(
                '/attendances',
                [StudentDataController::class, 'attendances']
            )->name('api.student.attendances');

            Route::get(
                '/signalements',
                [StudentDataController::class, 'signalements']
            )->name('api.student.signalements');
        });
});
