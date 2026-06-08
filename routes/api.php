<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\ProfileController;
use App\Http\Controllers\Api\V1\ResumeController;
use App\Http\Controllers\Api\V1\ResumeAnalysisController;
use App\Http\Controllers\Api\V1\ApplicationController;
use App\Http\Controllers\Api\V1\JobMatchController;
use App\Http\Controllers\Api\V1\CoverLetterController;
use App\Http\Controllers\Api\V1\InterviewPrepController;
use App\Http\Controllers\Api\V1\DashboardController;
use Illuminate\Support\Facades\Route;


Route::prefix('v1')->group(function (): void {

    // Auth routes
    Route::prefix('auth')->group(function (): void {
        Route::post('register', [AuthController::class, 'register']);
        Route::post('login', [AuthController::class, 'login']);
        Route::post('forgot-password', [AuthController::class, 'forgotPassword']);
        Route::post('reset-password', [AuthController::class, 'resetPassword']);
        Route::get('email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])
            ->middleware(['signed', 'throttle:6,1'])
            ->name('verification.verify');
        Route::middleware('auth:sanctum')->group(function (): void {
            Route::post('logout', [AuthController::class, 'logout']);
            Route::post('logout-all', [AuthController::class, 'logoutAll']);
            Route::post('email/verification-notification', [AuthController::class, 'sendVerificationEmail'])
                ->middleware('throttle:6,1');
        });
 
    });

    // Profile routes
    Route::middleware(['auth:sanctum', 'verified'])->prefix('profile')->group(function (): void {
        Route::get('/', [ProfileController::class, 'show']);
        Route::put('/', [ProfileController::class, 'update']);
        Route::put('password', [ProfileController::class, 'updatePassword']);
        Route::post('avatar', [ProfileController::class, 'uploadAvatar']);
    });

    // Resume routes
    Route::middleware('auth:sanctum')->prefix('resumes')->name('resumes.')->group(function (): void {
        Route::post('upload', [ResumeController::class, 'upload'])->name('upload');
        Route::get('/', [ResumeController::class, 'index'])->name('index');
        Route::get('{id}', [ResumeController::class, 'show'])->name('show');
        Route::put('{id}', [ResumeController::class, 'update'])->name('update');
        Route::delete('{id}', [ResumeController::class, 'destroy'])->name('destroy');
        Route::post('{id}/set-primary', [ResumeController::class, 'setPrimary'])->name('set-primary');
        Route::get('{id}/analyses', [ResumeAnalysisController::class, 'resumeAnalyses'])->name('analyses');
    });

    // Analysis routes
    Route::middleware('auth:sanctum')->prefix('analyses')->name('analyses.')->group(function (): void {
        Route::post('/', [ResumeAnalysisController::class, 'store'])->name('store');
        Route::get('/', [ResumeAnalysisController::class, 'index'])->name('index');
        Route::get('{id}', [ResumeAnalysisController::class, 'show'])->name('show');
        Route::delete('{id}', [ResumeAnalysisController::class, 'destroy'])->name('destroy');
    });
           // Application routes
    Route::middleware('auth:sanctum')->prefix('applications')->name('applications.')->group(function (): void {
    Route::get('/', [ApplicationController::class, 'index'])->name('index');
    Route::post('/', [ApplicationController::class, 'store'])->name('store');
    Route::put('{id}', [ApplicationController::class, 'update'])->name('update');
    Route::patch('{id}/status', [ApplicationController::class, 'updateStatus'])->name('status');
    Route::delete('{id}', [ApplicationController::class, 'destroy'])->name('destroy');
});
Route::middleware('auth:sanctum')->group(function (): void {
    Route::post('job-match', [JobMatchController::class, 'match']);
});

Route::middleware('auth:sanctum')->group(function (): void {
    Route::post('cover-letter', [CoverLetterController::class, 'generate']);
    Route::post('interview-questions', [InterviewPrepController::class, 'generate']);
});

// Dashboard
Route::middleware('auth:sanctum')->group(function (): void {
    Route::get('dashboard/stats', [DashboardController::class, 'stats']);
});

});