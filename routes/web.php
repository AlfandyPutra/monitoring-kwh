<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EnergyDashboardController;

Route::get('/', [EnergyDashboardController::class, 'index'])->name('dashboard');
Route::post('/simulate-fetch', [EnergyDashboardController::class, 'simulateFetch'])->name('simulate.fetch');