<?php

use App\Http\Controllers\PasienHewanController;
use Illuminate\Support\Facades\Route;

// Redirect root ke route utama
Route::get('/', function () {
    return redirect()->route('pasien.index');
});

// Route CRUD Pasien Hewan (Tipe D)
Route::get('/pasien-hewan', [PasienHewanController::class, 'index'])->name('pasien.index');
Route::post('/pasien-hewan', [PasienHewanController::class, 'store'])->name('pasien.store');
Route::get('/pasien-hewan/{id}/edit', [PasienHewanController::class, 'edit'])->name('pasien.edit');
Route::put('/pasien-hewan/{id}', [PasienHewanController::class, 'update'])->name('pasien.update');
Route::delete('/pasien-hewan/{id}', [PasienHewanController::class, 'destroy'])->name('pasien.destroy');
