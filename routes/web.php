<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HealthCheckController;
use App\Http\Controllers\ManagedApplicationController;

Route::get('/health', HealthCheckController::class)->name('health');

Route::middleware(['auth'])->group(function () {
    Route::get('/', DashboardController::class)->name('dashboard');

    Route::resource('applications', ManagedApplicationController::class);

    Route::post('applications/{application}/disconnect', [ManagedApplicationController::class, 'disconnect'])
        ->name('applications.disconnect');

    Route::post('applications/{application}/revoke', [ManagedApplicationController::class, 'revoke'])
        ->name('applications.revoke');

    Route::post('applications/{application}/regenerate-credentials', [ManagedApplicationController::class, 'regenerateCredentials'])
        ->name('applications.regenerate-credentials');

    Route::post('applications/{application}/test-connection', [ManagedApplicationController::class, 'testConnection'])
        ->name('applications.test-connection');

    Route::post('applications/{application}/generate-enrollment-token', [ManagedApplicationController::class, 'generateEnrollmentToken'])
        ->name('applications.generate-enrollment-token');

    // Health check routes
    Route::prefix('applications/{application}/health')->name('applications.health.')->group(function () {
        Route::get('/', [\App\Http\Controllers\ApplicationHealthController::class, 'index'])->name('index');
        Route::post('/check', [\App\Http\Controllers\ApplicationHealthController::class, 'check'])->name('check');
        Route::get('/{healthCheck}', [\App\Http\Controllers\ApplicationHealthController::class, 'show'])->name('show');
    });

    // Operation routes
    Route::prefix('applications/{application}/operations')->name('applications.operations.')->group(function () {
        Route::get('/', [\App\Http\Controllers\OperationController::class, 'index'])->name('index');
        Route::get('/create/{operation?}', [\App\Http\Controllers\OperationController::class, 'create'])->name('create');
        Route::post('/execute', [\App\Http\Controllers\OperationController::class, 'store'])->name('store');
        Route::get('/{operation}', [\App\Http\Controllers\OperationController::class, 'show'])->name('show');
        Route::post('/{operation}/confirm', [\App\Http\Controllers\OperationController::class, 'confirm'])->name('confirm');
    });

    // Log viewer routes
    Route::prefix('applications/{application}/logs')->name('applications.logs.')->group(function () {
        Route::get('/', [\App\Http\Controllers\LogViewerController::class, 'index'])->name('index');
        Route::post('/clear', [\App\Http\Controllers\LogViewerController::class, 'clear'])->name('clear');
    });

    // Queue monitor routes
    Route::prefix('applications/{application}/queue')->name('applications.queue.')->group(function () {
        Route::get('/', [\App\Http\Controllers\QueueMonitorController::class, 'index'])->name('index');
        Route::post('/retry-all', [\App\Http\Controllers\QueueMonitorController::class, 'retryAll'])->name('retry-all');
        Route::post('/purge', [\App\Http\Controllers\QueueMonitorController::class, 'purge'])->name('purge');
    });

    // Scheduler monitor routes
    Route::prefix('applications/{application}/scheduler')->name('applications.scheduler.')->group(function () {
        Route::get('/', [\App\Http\Controllers\SchedulerMonitorController::class, 'index'])->name('index');
        Route::post('/run-task', [\App\Http\Controllers\SchedulerMonitorController::class, 'runTask'])->name('run-task');
    });
});

// Enrollment validation API (for connector to call)
Route::post('/api/enrollment/validate', [\App\Http\Controllers\EnrollmentApiController::class, 'validateToken'])
    ->name('api.enrollment.validate');

require __DIR__.'/auth.php';
