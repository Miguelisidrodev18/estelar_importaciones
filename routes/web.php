<?php

use App\Http\Controllers\Auth\MasterPasswordController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\DirectPasswordResetController;
use App\Http\Controllers\DashboardController;
use App\Http\Middleware\VerifyMasterPassword;
use App\Http\Middleware\CheckRole;
use Illuminate\Support\Facades\Route;

// ========================================
// RUTA PRINCIPAL
// ========================================
Route::get('/', function () {
    return redirect()->route('login');
});

// ========================================
// RUTAS DE CONTRASEÑA MAESTRA
// ========================================
Route::middleware('guest')->group(function () {
    Route::get('/master-password', [MasterPasswordController::class, 'show'])
        ->name('master-password.show');
    
    Route::post('/master-password', [MasterPasswordController::class, 'verify'])
        ->name('master-password.verify');
});

// ========================================
// RUTAS DE AUTENTICACIÓN
// ========================================
Route::middleware('guest')->group(function () {
    // Login
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])
        ->name('login');
    
    Route::post('/login', [AuthenticatedSessionController::class, 'store']);

    // Registro - REQUIERE CONTRASEÑA MAESTRA
    Route::middleware(VerifyMasterPassword::class)->group(function () {
        Route::get('/register', [RegisteredUserController::class, 'create'])
            ->name('register');
        
        Route::post('/register', [RegisteredUserController::class, 'store']);
    });

    // Restablecimiento de contraseña - REQUIERE CONTRASEÑA MAESTRA
    Route::middleware(VerifyMasterPassword::class)->group(function () {
        Route::get('/forgot-password', [DirectPasswordResetController::class, 'show'])
            ->name('password.request');
        
        Route::post('/forgot-password', [DirectPasswordResetController::class, 'update'])
            ->name('password.update-direct');
    });
});

// ========================================
// DASHBOARDS POR ROL
// ========================================

// Dashboard Administrador
Route::middleware(['auth', 'role:Administrador'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'admin'])->name('dashboard');
});

// Dashboard Vendedor
Route::middleware(['auth', 'role:Vendedor'])->prefix('vendedor')->name('vendedor.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'vendedor'])->name('dashboard');
});

// Dashboard Almacenero
Route::middleware(['auth', 'role:Almacenero'])->prefix('almacenero')->name('almacenero.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'almacenero'])->name('dashboard');
});

// Dashboard Cajero
Route::middleware(['auth', 'role:Cajero'])->prefix('cajero')->name('cajero.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'cajero'])->name('dashboard');
});

// Dashboard Proveedor
Route::middleware(['auth', 'role:Proveedor'])->prefix('proveedor')->name('proveedor.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'proveedor'])->name('dashboard');
});

// ========================================
// RUTAS PROTEGIDAS GENERALES
// ========================================
Route::middleware('auth')->group(function () {
    // Logout
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');
});