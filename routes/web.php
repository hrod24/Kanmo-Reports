<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UploadController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\DashboardController;


Route::middleware([
    'office.network',
])->group(function () {

    Route::redirect('/', '/dashboard');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/reports', [ReportController::class,'index'])->name('reports');
    Route::get('/reports/{type}', [ReportController::class,'show'])->name('reports.show');
    Route::get('/upload-data', [UploadController::class, 'index'])->name('upload.index');
    Route::post('/upload-data', [UploadController::class, 'store'])->name('upload.store');
    Route::get('/upload-data', [UploadController::class, 'index'])->name('upload.index');
    Route::post('/upload-data', [UploadController::class, 'store'])->name('upload.store');
    Route::get('/upload-data/progress/{history}', [UploadController::class, 'progress'])->name('upload.progress');
    Route::post('/upload-data/process/{history}', [UploadController::class, 'process'])->name('upload.process');
});