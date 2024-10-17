<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\BarangController;
use App\Http\Controllers\Api\PelangganController;
use App\Http\Controllers\Api\PenjualanController;

Route::prefix('barang')
    ->controller(BarangController::class)
    ->group(function () {
        Route::get('/', 'index'); 
        Route::post('/', 'store'); 
        Route::get('{barangId}', 'show'); 
        Route::put('{barangId}', 'update'); 
        Route::delete('{barangId}', 'destroy'); 
    });

Route::prefix('pelanggan')
    ->controller(PelangganController::class)
    ->group(function () {
        Route::get('/', 'index'); 
        Route::post('/', 'store'); 
        Route::get('{pelangganId}', 'show'); 
        Route::put('{pelangganId}', 'update'); 
        Route::delete('{pelangganId}', 'destroy'); 
    });

Route::prefix('penjualan')
    ->controller(PenjualanController::class)
    ->group(function () {
        Route::get('/', 'index'); 
        Route::post('/', 'store'); 
        Route::get('{notaId}', 'show'); 
        Route::put('{notaId}', 'update'); 
        Route::delete('{notaId}', 'destroy'); 
    });
