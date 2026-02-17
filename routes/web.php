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

// ===================== NUEVOS MÓDULOS =====================
use App\Http\Controllers\ProveedorController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\PedidoController;
use App\Http\Controllers\CompraController;
use App\Http\Controllers\VentaController;
use App\Http\Controllers\TrasladoController;
use App\Http\Controllers\CajaController;

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
// ========================================
// MÓDULO DE USUARIOS
// ========================================
Route::middleware(['auth', 'role:Administrador'])->prefix('users')->name('users.')->group(function () {
    Route::get('/', [App\Http\Controllers\UserController::class, 'index'])->name('index');
    Route::get('/create', [App\Http\Controllers\UserController::class, 'create'])->name('create');
    Route::post('/', [App\Http\Controllers\UserController::class, 'store'])->name('store');
    Route::get('/{user}/edit', [App\Http\Controllers\UserController::class, 'edit'])->name('edit');
    Route::get('/{user}', [App\Http\Controllers\UserController::class, 'show'])->name('show');
    Route::put('/{user}', [App\Http\Controllers\UserController::class, 'update'])->name('update');
    Route::delete('/{user}', [App\Http\Controllers\UserController::class, 'destroy'])->name('destroy');
    Route::get('/{user}/edit', [App\Http\Controllers\UserController::class, 'edit'])->name('edit');
    Route::put('/{user}', [App\Http\Controllers\UserController::class, 'update'])->name('update');
    Route::delete('/{user}', [App\Http\Controllers\UserController::class, 'destroy'])->name('destroy');
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
            
            Route::get('movimientos/imeis-disponibles', [MovimientoInventarioController::class, 'getImeisDisponibles'])
                ->name('movimientos.imeis-disponibles');

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
        Route::get('/tienda/dashboard', [DashboardController::class, 'tienda'])->name('tienda.dashboard')
                ->name('consulta-tienda');
    });
    });
// ========================================
// MÓDULO DE PROVEEDORES
// ========================================
Route::middleware(['auth', 'role:Administrador,Almacenero'])->prefix('proveedores')->name('proveedores.')->group(function () {
    Route::get('/', [ProveedorController::class, 'index'])->name('index');
    Route::get('/create', [ProveedorController::class, 'create'])->name('create');
    Route::post('/', [ProveedorController::class, 'store'])->name('store');
    Route::get('/{proveedor}', [ProveedorController::class, 'show'])->name('show');
    Route::get('/{proveedor}/edit', [ProveedorController::class, 'edit'])->name('edit');
    Route::put('/{proveedor}', [ProveedorController::class, 'update'])->name('update');
    Route::delete('/{proveedor}', [ProveedorController::class, 'destroy'])->middleware('role:Administrador')->name('destroy');
    Route::post('/consultar-sunat', [ProveedorController::class, 'consultarSunat'])->name('consultar-sunat');
});

// ========================================
// MÓDULO DE CLIENTES
// ========================================
Route::middleware(['auth', 'role:Administrador,Vendedor,Tienda'])->prefix('clientes')->name('clientes.')->group(function () {
    Route::get('/', [ClienteController::class, 'index'])->name('index');
    Route::get('/create', [ClienteController::class, 'create'])->name('create');
    Route::post('/', [ClienteController::class, 'store'])->name('store');
    Route::get('/{cliente}/edit', [ClienteController::class, 'edit'])->name('edit');
    Route::put('/{cliente}', [ClienteController::class, 'update'])->name('update');
    Route::delete('/{cliente}', [ClienteController::class, 'destroy'])->middleware('role:Administrador')->name('destroy');
    Route::post('/consultar-documento', [ClienteController::class, 'consultarDocumento'])->name('consultar-documento');
});

// ========================================
// MÓDULO DE PEDIDOS
// ========================================
Route::middleware(['auth', 'role:Administrador,Almacenero'])->prefix('pedidos')->name('pedidos.')->group(function () {
    Route::get('/', [PedidoController::class, 'index'])->name('index');
    Route::get('/create', [PedidoController::class, 'create'])->name('create');
    Route::post('/', [PedidoController::class, 'store'])->name('store');
    Route::get('/{pedido}', [PedidoController::class, 'show'])->name('show');
    Route::patch('/{pedido}/estado', [PedidoController::class, 'cambiarEstado'])->name('cambiar-estado');
});

Route::middleware(['auth', 'role:Proveedor'])->group(function () {
    Route::get('/proveedor/pedidos', [PedidoController::class, 'pedidosProveedor'])->name('proveedor.pedidos');
});

// ========================================
// MÓDULO DE COMPRAS
// ========================================
Route::middleware(['auth', 'role:Administrador,Almacenero'])->prefix('compras')->name('compras.')->group(function () {
    Route::get('/', [CompraController::class, 'index'])->name('index');
    Route::get('/create', [CompraController::class, 'create'])->name('create');
    Route::post('/', [CompraController::class, 'store'])->name('store');
    Route::get('/{compra}', [CompraController::class, 'show'])->name('show');
});

// ========================================
// MÓDULO DE VENTAS
// ========================================
Route::middleware(['auth', 'role:Administrador,Vendedor,Tienda'])->prefix('ventas')->name('ventas.')->group(function () {
    Route::get('/', [VentaController::class, 'index'])->name('index');
    Route::get('/create', [VentaController::class, 'create'])->name('create');
    Route::post('/', [VentaController::class, 'store'])->name('store');
    Route::get('/{venta}', [VentaController::class, 'show'])->name('show');
    Route::post('/{venta}/confirmar-pago', [VentaController::class, 'confirmarPago'])
        ->middleware('role:Administrador,Tienda')
        ->name('confirmar-pago');
    Route::get('/api/imeis-disponibles', [VentaController::class, 'imeisDisponibles'])->name('imeis-disponibles');
});

// ========================================
// MÓDULO DE TRASLADOS
// ========================================
// ========================================
// MÓDULO DE TRASLADOS - CORREGIDO
// ========================================
Route::middleware(['auth'])->prefix('traslados')->name('traslados.')->group(function () {
    
    // PRIMERO: Rutas específicas (sin parámetros)
    Route::middleware('role:Administrador,Almacenero')->group(function () {
        Route::get('/', [TrasladoController::class, 'index'])->name('index');
        Route::get('/create', [TrasladoController::class, 'create'])->name('create');
        Route::post('/', [TrasladoController::class, 'store'])->name('store');
    });

    // Rutas de recepción (Admin, Almacenero y Tienda)
    Route::middleware('role:Administrador,Almacenero,Tienda')->group(function () {
        Route::get('/pendientes', [TrasladoController::class, 'pendientes'])->name('pendientes');
        Route::post('/{traslado}/confirmar', [TrasladoController::class, 'confirmar'])->name('confirmar');
    });

    // ÚLTIMO: Rutas con parámetros dinámicos
    Route::middleware('role:Administrador,Almacenero')->group(function () {
        Route::get('/{traslado}', [TrasladoController::class, 'show'])->name('show');
    });
});

// ========================================
// MÓDULO DE CAJA
// ========================================
Route::middleware(['auth', 'role:Administrador,Tienda'])->prefix('caja')->name('caja.')->group(function () {
    Route::get('/', [CajaController::class, 'index'])->name('index');
    Route::get('/abrir', [CajaController::class, 'abrir'])->name('abrir');
    Route::post('/abrir', [CajaController::class, 'store'])->name('store');
    Route::get('/actual', [CajaController::class, 'actual'])->name('actual');
    Route::post('/cerrar', [CajaController::class, 'cerrar'])->name('cerrar');
});
    /*
    |--------------------------------------------------------------------------
    | LOGOUT
    |--------------------------------------------------------------------------
    */
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');
});
