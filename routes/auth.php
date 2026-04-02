<?php

use App\Enums\AuthRouteNames;
use App\Enums\TokenAbility;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\VerifyEmailController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->middleware(['ensure.json', 'JsonAPI.parse'])->group(function () {
    Route::post('/register', [RegisteredUserController::class, 'store'])
        ->middleware('guest')
        ->name(AuthRouteNames::REGISTER->value);

    Route::post('/set-password', [NewPasswordController::class, 'setPassword'])
        ->middleware('guest')
        ->name(AuthRouteNames::SET_PASSWORD->value);

    Route::post('/login', [AuthenticatedSessionController::class, 'store'])
        ->middleware('guest')
        ->name(AuthRouteNames::LOGIN->value);

    Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])
        ->middleware('guest')
        ->name(AuthRouteNames::FORGOT_PASSWORD->value);

    Route::post('/reset-password', [NewPasswordController::class, 'store'])
        ->middleware('guest')
        ->name(AuthRouteNames::RESET_PASSWORD->value);

    Route::get('/refresh-token', [AuthenticatedSessionController::class, 'refreshToken'])
        ->middleware(['auth:sanctum', 'ability:'.TokenAbility::ISSUE_ACCESS_TOKEN->value])
        ->name(AuthRouteNames::REFRESH_TOKEN->value);

    Route::get('/verify-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['auth:sanctum', 'signed', 'throttle:6,1'])
        ->name(AuthRouteNames::VERIFY_EMAIL->value);

    Route::post('/email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
        ->middleware(['auth:sanctum', 'throttle:6,1'])
        ->name(AuthRouteNames::EMAIL_VERIFY_NOTIFICATION->value);

    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
        ->middleware('auth:sanctum')
        ->name(AuthRouteNames::LOGOUT->value);
});
