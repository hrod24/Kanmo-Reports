<?php

use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');

Route::view('/dashboard', 'dashboard')
    ->name('dashboard');

Route::view('/reports', 'reports')
    ->name('reports');

Route::view('/upload-data', 'upload-data')
    ->name('upload-data');