<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

// ===================== AUTH =====================
use App\Http\Controllers\Auth\MasterPasswordController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\DirectPasswordResetController;
use App\Http\Controllers\ProfileController;

// ===================== CORE =====================
use App\Http\Controllers\DashboardController;

// ===================== INVENTARIO =====================
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\MovimientoInventarioController;
use App\Http\Controllers\AlmacenController;
use App\Http\Controllers\ImeiController;


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
        'Tienda'        => redirect()->route('tienda.dashboard'),
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

    Route::middleware('role:Tienda')->prefix('tienda')->name('tienda.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'tienda'])->name('dashboard');
    });

    Route::middleware('role:Proveedor')->prefix('proveedor')->name('proveedor.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'proveedor'])->name('dashboard');
    });
// ========================================
// RUTAS DE PERFIL
// ========================================
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});
// ========================================
// MÓDULO DE INVENTARIO
// ========================================
Route::middleware(['auth'])->prefix('inventario')->name('inventario.')->group(function () {
    
    // ============================================
    // CATEGORÍAS (Admin y Almacenero)
    // ============================================
    Route::middleware('role:Administrador,Almacenero')->group(function () {
        Route::get('/categorias', [CategoriaController::class, 'index'])
            ->name('categorias.index');
        Route::get('/categorias/create', [CategoriaController::class, 'create'])
            ->name('categorias.create');
        Route::post('/categorias', [CategoriaController::class, 'store'])
            ->name('categorias.store');
        Route::get('/categorias/{categoria}/edit', [CategoriaController::class, 'edit'])
            ->name('categorias.edit');
        Route::put('/categorias/{categoria}', [CategoriaController::class, 'update'])
            ->name('categorias.update');
        Route::delete('/categorias/{categoria}', [CategoriaController::class, 'destroy'])
            ->middleware('role:Administrador')
            ->name('categorias.destroy');
        Route::get('/categorias/{categoria}', [CategoriaController::class, 'show'])
            ->name('categorias.show');
    });

    // ============================================
    // PRODUCTOS (Admin y Almacenero para gestión, Tienda para consulta)
    // ============================================
        Route::middleware('role:Administrador,Almacenero')->group(function () {
        Route::get('/productos/create', [ProductoController::class, 'create'])
            ->name('productos.create');
        Route::post('/productos', [ProductoController::class, 'store'])
            ->name('productos.store');
        Route::get('/productos/{producto}/edit', [ProductoController::class, 'edit'])
            ->name('productos.edit');
        Route::put('/productos/{producto}', [ProductoController::class, 'update'])
            ->name('productos.update');
        Route::delete('/productos/{producto}', [ProductoController::class, 'destroy'])
            ->middleware('role:Administrador')
            ->name('productos.destroy');
    });
    route::get('/productos', [ProductoController::class, 'index'])
            ->name('productos.index');
    Route::get('/productos/{producto}', [ProductoController::class, 'show'])
            ->name('productos.show');
    Route::get('/productos/buscar', [ProductoController::class, 'buscarAjax'])
            ->name('productos.buscar-ajax');
    Route::get('/productos/consulta-tienda', [ProductoController::class, 'consultaTienda'])
            ->middleware('role:Tienda')
            ->name('productos.consulta-tienda');

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

    /*
    |-------------------------
    | GESTIÓN DE IMEIs (Solo para CELULARES)
    |-------------------------
    */
    Route::middleware('role:Administrador,Almacenero')->group(function () {
        Route::get('/imeis', [ImeiController::class, 'index'])
            ->name('imeis.index');
        Route::get('/imeis/create', [ImeiController::class, 'create'])
            ->name('imeis.create');
        Route::post('/imeis', [ImeiController::class, 'store'])
            ->name('imeis.store');
        Route::get('/imeis/{imei}', [ImeiController::class, 'show'])
            ->name('imeis.show');
        Route::get('/imeis/{imei}/edit', [ImeiController::class, 'edit'])
            ->name('imeis.edit');
        Route::put('/imeis/{imei}', [ImeiController::class, 'update'])
            ->name('imeis.update');
        
        // API para obtener IMEIs disponibles por producto y almacén
        Route::get('/api/imeis-disponibles', [ImeiController::class, 'getImeisDisponibles'])
            ->name('imeis.disponibles');
    });
//=========================================
// RUTA DE CONSULTA PARA TIENDA
//=========================================
    // Consulta de inventario para tienda (solo lectura)
    Route::middleware(['auth', 'role:Tienda'])->group(function () {
        Route::get('/consulta', [ProductoController::class, 'consultaTienda'])
                ->name('consulta-tienda');
    });
    });
// ========================================
// RUTAS FUTURAS (Preparadas)
// ========================================

// Módulo de Compras (Día 3)
// Route::prefix('compras')->name('compras.')->group(function () {
//     // Rutas de compras aquí
// });

// Módulo de Ventas (Día 4)
// Route::prefix('ventas')->name('ventas.')->group(function () {
//     // Rutas de ventas aquí
// });

// Módulo de Reportes (Día 5)
// Route::prefix('reportes')->name('reportes.')->group(function () {
//     // Rutas de reportes aquí
// });
    /*
    |--------------------------------------------------------------------------
    | LOGOUT
    |--------------------------------------------------------------------------
    */
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');
});
