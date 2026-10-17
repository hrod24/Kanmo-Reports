<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UploadController;
use App\Http\Controllers\ReportController;


Route::middleware([
    'office.network',
])->group(function () {

    Route::redirect('/', '/dashboard');
    Route::view('/dashboard', 'dashboard')->name('dashboard');
    Route::get('/reports', [ReportController::class,'index'])->name('reports');
    Route::get('/reports/{type}', [ReportController::class,'show'])->name('reports.show');
    Route::get('/upload-data', [UploadController::class, 'index'])->name('upload.index');
    Route::post('/upload-data', [UploadController::class, 'store'])->name('upload.store');
});