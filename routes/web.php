<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\PrincipalController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store']);
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [PrincipalController::class, 'index'])->name('dashboard');
    Route::get('/principal/updated-at', [PrincipalController::class, 'updatedAt'])->name('principal.updated-at');
    Route::get('/principal/search', [PrincipalController::class, 'search'])->name('principal.search');
    Route::get('/principal/state-totals', [PrincipalController::class, 'stateTotals'])->name('principal.state-totals');
    Route::get('/principal/states/{uf}', [PrincipalController::class, 'stateRecords'])->name('principal.states.show');
    Route::get('/principal/states/{uf}/download', [PrincipalController::class, 'downloadState'])->name('principal.states.download');
    Route::get('/principal/download', [PrincipalController::class, 'downloadAll'])->name('principal.download');

    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
});
