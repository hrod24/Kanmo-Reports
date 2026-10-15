<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UploadController;

Route::redirect('/', '/dashboard');

Route::view('/dashboard', 'dashboard')
    ->name('dashboard');

Route::view('/reports', 'reports')
    ->name('reports');

Route::get('/upload-data', [UploadController::class, 'index'])
    ->name('upload.index');

Route::post('/upload-data', [UploadController::class, 'store'])
    ->name('upload.store');