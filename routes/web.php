<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\SampleController;
use App\Http\Controllers\WorkflowController;
use App\Http\Controllers\ReportPdfController;

/*
|--------------------------------------------------------------------------
| Public redirect
|--------------------------------------------------------------------------
*/
Route::get('/', fn() => auth()->check()
    ? redirect()->route('dashboard')
    : redirect()->route('login'));

/*
|--------------------------------------------------------------------------
| Auth (guest / auth)
|--------------------------------------------------------------------------
*/
Route::get('/login',  [AuthController::class, 'showLogin'])->name('login')->middleware('guest');
Route::post('/login', [AuthController::class, 'login'])->middleware('guest');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

/*
|--------------------------------------------------------------------------
| Protected (must login)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {

    Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');

    // ===== Samples =====
    Route::get('/samples/data',          [SampleController::class, 'data'])->name('samples.data');
    Route::get('/samples',               [SampleController::class, 'index'])->name('samples.index');
    Route::get('/samples/new',           [SampleController::class, 'create'])->name('samples.create');
    Route::post('/samples',              [SampleController::class, 'store'])->name('samples.store');
	
	// Submit draft → SUBMITTED
	Route::post('/samples/{sample}/submit', [\App\Http\Controllers\WorkflowController::class, 'submit'])
    ->name('samples.submit');

    Route::post('/samples/{sample}/revoke', [\App\Http\Controllers\WorkflowController::class, 'revoke'])
    ->name('samples.revoke');


    Route::get('/samples/{sample}/edit', [SampleController::class, 'edit'])->name('samples.edit');
    Route::put('/samples/{sample}',      [SampleController::class, 'update'])->name('samples.update');

    // Soft delete (ke Recycle Bin)
    Route::delete('/samples/{sample}',   [SampleController::class,'destroy'])->name('samples.destroy');

    // ===== Approvals =====
    Route::get('/approvals',             [WorkflowController::class, 'queue'])->name('approvals.index');
    Route::post('/approvals/{sample}',   [WorkflowController::class, 'approve'])->name('approvals.approve');
    Route::post('/approvals/{sample}/reject', [WorkflowController::class,'reject'])->name('approvals.reject');

    // ===== Reports (preview / download) =====
    Route::get('/reports/daily',         [\App\Http\Controllers\DailyReportController::class, 'index'])->name('reports.daily');
    Route::get('/reports/{sample}/pdf',  [ReportPdfController::class, 'show'])->name('reports.pdf');

    // ===== Mill Certificate Generator =====
    Route::get('/mill-certificate',      [\App\Http\Controllers\MillCertificateController::class, 'index'])->name('mill-certificate.index');
    Route::get('/mill-certificate/generate', [\App\Http\Controllers\MillCertificateController::class, 'generate'])->name('mill-certificate.generate');

    // ===== Heat Number Checker =====
    Route::get('/heat-number-checker', [\App\Http\Controllers\HeatNumberCheckerController::class, 'index'])->name('checker.index');
    Route::post('/heat-number-checker/verify', [\App\Http\Controllers\HeatNumberCheckerController::class, 'verify'])->name('checker.verify');

    // ===== Mechanical Testing =====
    Route::get('/mechanical/data',                 [\App\Http\Controllers\MechanicalController::class, 'data'])->name('mechanical.data');
    Route::get('/mechanical',                 [\App\Http\Controllers\MechanicalController::class, 'index'])->name('mechanical.index');
    Route::get('/mechanical/{sample}/edit',    [\App\Http\Controllers\MechanicalController::class, 'edit'])->name('mechanical.edit');
    Route::put('/mechanical/{sample}',         [\App\Http\Controllers\MechanicalController::class, 'update'])->name('mechanical.update');

    // ===== Recycle Bin =====
    Route::get('/recycle-bin',                 [SampleController::class,'recycle'])->name('samples.recycle');
    Route::post('/recycle-bin/{id}/restore',   [SampleController::class,'restore'])->name('samples.restore');
    Route::delete('/recycle-bin/{id}/force',   [SampleController::class,'forceDelete'])->name('samples.force');
});
