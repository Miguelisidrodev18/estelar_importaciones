<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

// ===================== AUTH =====================
use App\Http\Controllers\Auth\MasterPasswordController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\DirectPasswordResetController;

// ===================== CORE =====================
use App\Http\Controllers\DashboardController;

// ===================== INVENTARIO =====================
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\MovimientoInventarioController;
use App\Http\Controllers\AlmacenController;

// ===================== MIDDLEWARE =====================
use App\Http\Middleware\VerifyMasterPassword;

/*
|--------------------------------------------------------------------------
| RUTA PRINCIPAL
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    if (!Auth::check()) {
        return redirect()->route('login');
    }

    $role = Auth::user()->role->nombre ?? null;

    return match ($role) {
        'Administrador' => redirect()->route('admin.dashboard'),
        'Almacenero'    => redirect()->route('almacenero.dashboard'),
        'Vendedor'      => redirect()->route('vendedor.dashboard'),
        'Cajero'        => redirect()->route('cajero.dashboard'),
        'Proveedor'     => redirect()->route('proveedor.dashboard'),
        default         => redirect()->route('login'),
    };
});

/*
|--------------------------------------------------------------------------
| CONTRASEÑA MAESTRA
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {

    Route::get('/master-password', [MasterPasswordController::class, 'show'])
        ->name('master-password.show');

    Route::post('/master-password', [MasterPasswordController::class, 'verify'])
        ->name('master-password.verify');
});

/*
|--------------------------------------------------------------------------
| AUTENTICACIÓN
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {

    Route::get('/login', [AuthenticatedSessionController::class, 'create'])
        ->name('login');

    Route::post('/login', [AuthenticatedSessionController::class, 'store']);

    Route::middleware(VerifyMasterPassword::class)->group(function () {

        Route::get('/register', [RegisteredUserController::class, 'create'])
            ->name('register');

        Route::post('/register', [RegisteredUserController::class, 'store']);

        Route::get('/forgot-password', [DirectPasswordResetController::class, 'show'])
            ->name('password.request');

        Route::post('/forgot-password', [DirectPasswordResetController::class, 'update'])
            ->name('password.update-direct');
    });
});

/*
|--------------------------------------------------------------------------
| RUTAS PROTEGIDAS (AUTH)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | DASHBOARDS POR ROL
    |--------------------------------------------------------------------------
    */
    Route::middleware('role:Administrador')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'admin'])->name('dashboard');
    });

    Route::middleware('role:Vendedor')->prefix('vendedor')->name('vendedor.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'vendedor'])->name('dashboard');
    });

    Route::middleware('role:Almacenero')->prefix('almacenero')->name('almacenero.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'almacenero'])->name('dashboard');
    });

    Route::middleware('role:Cajero')->prefix('cajero')->name('cajero.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'cajero'])->name('dashboard');
    });

    Route::middleware('role:Proveedor')->prefix('proveedor')->name('proveedor.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'proveedor'])->name('dashboard');
    });

    /*
    |--------------------------------------------------------------------------
    | INVENTARIO (CATEGORÍAS, PRODUCTOS, MOVIMIENTOS)
    |--------------------------------------------------------------------------
    */
    Route::prefix('inventario')->name('inventario.')->group(function () {

        /*
        |-------------------------
        | CATEGORÍAS
        |-------------------------
        */
        Route::get('/categorias', [CategoriaController::class, 'index'])
            ->name('categorias.index');

        Route::get('/categorias/create', [CategoriaController::class, 'create'])
            ->middleware('role:Administrador,Almacenero')
            ->name('categorias.create');

        Route::post('/categorias', [CategoriaController::class, 'store'])
            ->middleware('role:Administrador,Almacenero')
            ->name('categorias.store');

        Route::get('/categorias/{categoria}/edit', [CategoriaController::class, 'edit'])
            ->middleware('role:Administrador,Almacenero')
            ->name('categorias.edit');

        Route::put('/categorias/{categoria}', [CategoriaController::class, 'update'])
            ->middleware('role:Administrador,Almacenero')
            ->name('categorias.update');

        Route::delete('/categorias/{categoria}', [CategoriaController::class, 'destroy'])
            ->middleware('role:Administrador')
            ->name('categorias.destroy');

        Route::get('/categorias/{categoria}', [CategoriaController::class, 'show'])
            ->name('categorias.show');

        /*
        |-------------------------
        | PRODUCTOS
        |-------------------------
        */
        Route::get('/productos', [ProductoController::class, 'index'])
            ->name('productos.index');

        Route::get('/productos/create', [ProductoController::class, 'create'])
            ->middleware('role:Administrador,Almacenero')
            ->name('productos.create');

        Route::post('/productos', [ProductoController::class, 'store'])
            ->middleware('role:Administrador,Almacenero')
            ->name('productos.store');

        Route::get('/productos/{producto}/edit', [ProductoController::class, 'edit'])
            ->middleware('role:Administrador,Almacenero')
            ->name('productos.edit');

        Route::put('/productos/{producto}', [ProductoController::class, 'update'])
            ->middleware('role:Administrador,Almacenero')
            ->name('productos.update');

        Route::delete('/productos/{producto}', [ProductoController::class, 'destroy'])
            ->middleware('role:Administrador')
            ->name('productos.destroy');

        Route::get('/productos/{producto}', [ProductoController::class, 'show'])
            ->name('productos.show');

        Route::get('/productos/buscar', [ProductoController::class, 'buscarAjax'])
            ->name('productos.buscar-ajax');

        /*
        |-------------------------
        | MOVIMIENTOS DE INVENTARIO
        |-------------------------
        */
        Route::middleware('role:Administrador,Almacenero')->group(function () {

            Route::get('/movimientos', [MovimientoInventarioController::class, 'index'])
                ->name('movimientos.index');

            Route::get('/movimientos/create', [MovimientoInventarioController::class, 'create'])
                ->name('movimientos.create');

            Route::post('/movimientos', [MovimientoInventarioController::class, 'store'])
                ->name('movimientos.store');

            Route::get('/movimientos/{movimiento}', [MovimientoInventarioController::class, 'show'])
                ->name('movimientos.show');

            Route::get('/api/stock-actual', [MovimientoInventarioController::class, 'getStockActual'])
                ->name('movimientos.stock-actual');
        });
        /*
        |-------------------------
        | MOVIMIENTOS DE INVENTARIO
        |-------------------------
        */
        // Solo Admin y Almacenero pueden gestionar almacenes
    Route::middleware('role:Administrador,Almacenero')->group(function () {
        
        Route::get('/almacenes', [AlmacenController::class, 'index'])
            ->name('almacenes.index');
        
        Route::get('/almacenes/create', [AlmacenController::class, 'create'])
            ->name('almacenes.create');
        
        Route::post('/almacenes', [AlmacenController::class, 'store'])
            ->name('almacenes.store');
        
        Route::get('/almacenes/{almacen}', [AlmacenController::class, 'show'])
            ->name('almacenes.show');
        
        Route::get('/almacenes/{almacen}/edit', [AlmacenController::class, 'edit'])
            ->name('almacenes.edit');
        
        Route::put('/almacenes/{almacen}', [AlmacenController::class, 'update'])
            ->name('almacenes.update');
        
        Route::delete('/almacenes/{almacen}', [AlmacenController::class, 'destroy'])
            ->middleware('role:Administrador')
            ->name('almacenes.destroy');
    });
    // Consulta de inventario para cajero (solo lectura)
    Route::middleware(['auth', 'role:Cajero'])->group(function () {
        Route::get('/consulta', [ProductoController::class, 'consultaCajero'])
                ->name('consulta-cajero');
    });
    });

    /*
    |--------------------------------------------------------------------------
    | LOGOUT
    |--------------------------------------------------------------------------
    */
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');
});
