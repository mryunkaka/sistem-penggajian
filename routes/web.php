<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PdfController;
use App\Http\Controllers\SlipGajiController;

Route::get('/', function () {
    return view('welcome');
});
// routes/web.php
Route::get('/pdf/generate-laporan-penggajian', [PdfController::class, 'generateLaporanPenggajian'])
    ->name('pdf.generate-laporan-penggajian');

Route::get('/slip-gaji/{id}', [SlipGajiController::class, 'generateSlip'])->name('slip-gaji');
