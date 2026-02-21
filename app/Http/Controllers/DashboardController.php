<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Venta;
use App\Models\Producto;
use App\Models\Cliente;
use App\Models\Almacen;
use App\Models\Caja;
use App\Models\MovimientoInventario;
use App\Models\Imei;
use App\Models\Proveedor;
use App\Models\StockAlmacen;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index()
{
    $user = auth()->user();
    $rol = $user->role->nombre;
    
    return match($rol) {
        'Administrador' => redirect()->route('admin.dashboard'),
        'Almacenero' => redirect()->route('almacenero.dashboard'),
        'Tienda' => redirect()->route('tienda.dashboard'), // CAMBIAR DE 'Cajero'
        'Vendedor' => redirect()->route('vendedor.dashboard'),
        'Proveedor' => redirect()->route('proveedor.dashboard'),
        default => abort(403, 'Rol no autorizado'),
    };
}
    /**
     * Dashboard del Administrador
     */
    public function admin(): View
    {
        $data = [
            'total_usuarios' => User::count(),
            'usuarios_activos' => User::where('estado', 'activo')->count(),
            'usuarios_inactivos' => User::where('estado', 'inactivo')->count(),
            'usuarios_por_rol' => User::join('roles', 'users.role_id', '=', 'roles.id')
                ->selectRaw('roles.nombre, COUNT(*) as total')
                ->groupBy('roles.nombre')
                ->get(),
            'ventas_totales' => Venta::where('estado_pago', 'pagado')->sum('total'),
            'stock_total' => StockAlmacen::sum('cantidad'),
            'stock_celulares' => Producto::where('productos.tipo_inventario', 'serie')
                ->join('stock_almacen', 'productos.id', '=', 'stock_almacen.producto_id')
                ->sum('stock_almacen.cantidad'),
            'imeis_totales' => Imei::count(),
            'imeis_disponibles' => Imei::where('estado_imei', 'en_stock')->count(),
            'productos_bajo_stock' => StockAlmacen::where('cantidad', '<', 10)->count(),
            'total_tiendas' => Almacen::where('tipo', 'tienda')->count(),
            'total_almacenes' => Almacen::where('tipo', 'almacen')->count(),
            'total_proveedores' => Proveedor::count(),
            'traslados_pendientes' => MovimientoInventario::where('tipo_movimiento', 'transferencia')
                ->where('estado', 'pendiente')
                ->count(),
            'ultimos_movimientos' => MovimientoInventario::with('producto', 'usuario')
                ->latest()
                ->limit(10)
                ->get(),
        ];

        return view('dashboards.admin', $data);
    }

    /**
     * Dashboard del Vendedor
     */
    public function vendedor(): View
    {
    $user = auth()->user();
    
    // Ventas externas del día
    $ventas_dia = Venta::where('tipo_venta', 'externa')
        ->where('user_id', $user->id)
        ->whereDate('fecha', today())
        ->sum('total');
    
    // Ventas pendientes de pago
    $ventas_pendientes = Venta::where('tipo_venta', 'externa')
        ->where('user_id', $user->id)
        ->where('estado_pago', 'pendiente')
        ->with('cliente', 'tiendaDestino')
        ->get();
    
    $total_por_cobrar = $ventas_pendientes->sum('total');
    
    // Ventas cobradas del mes
    $ventas_mes = Venta::where('tipo_venta', 'externa')
        ->where('user_id', $user->id)
        ->where('estado_pago', 'pagado')
        ->whereMonth('fecha', now()->month)
        ->whereYear('fecha', now()->year)
        ->sum('total');
    
    // Tiendas disponibles para enviar clientes
    $tiendas = User::whereHas('role', function($query) {
            $query->where('nombre', 'Tienda'); // Ajusta el nombre exacto del rol
        })
        ->orderBy('name')
        ->get(['id', 'name']);
    
    // Últimas ventas
    $ultimas_ventas = Venta::where('user_id', $user->id)
        ->with('cliente', 'tiendaDestino')
        ->orderBy('created_at', 'desc')
        ->limit(10)
        ->get();
    
    $data = [
        'ventas_hoy' => $ventas_dia,
        'ventas_mes' => $ventas_mes,
        'clientes_atendidos' => $ventas_pendientes->count(),
        'productos_vendidos' => 0, // Lo puedes calcular después
        'ventas_pendientes' => $ventas_pendientes,
        'total_por_cobrar' => $total_por_cobrar,
        'tiendas' => $tiendas,
        'ultimas_ventas' => $ultimas_ventas,
    ];

    return view('dashboards.vendedor', $data);
}

    /**
     * Dashboard del Almacenero
     */
    public function almacenero(): View
    {
        $data = [
            'productos_stock' => 0, // Placeholder - se implementará en el módulo de inventario
            'productos_bajo_stock' => 0,
            'movimientos_hoy' => 0,
            'almacenes_activos' => 0,
        ];

        return view('dashboards.almacenero', $data);
    }

    /**
     * Dashboard del Proveedor
     */
    public function proveedor(): View
    {
        $data = [
            'ordenes_pendientes' => 0, // Placeholder - se implementará en el módulo de compras
            'ordenes_completadas' => 0,
            'productos_catalogo' => 0,
            'monto_total' => 0,
        ];

        return view('dashboards.proveedor', $data);
    }

    /**
     * Dashboard del Tienda
     */
    
public function tienda()
{
    $user = auth()->user();
    $hoy = now()->toDateString();
    
    // Calcular todas las variables que necesita la vista
    $ventas_dia = Venta::where('user_id', $user->id)
        ->whereDate('fecha', $hoy)
        ->where('estado_pago', 'pagado')
        ->sum('total');
    
    $transacciones_dia = Venta::where('user_id', $user->id)
        ->whereDate('fecha', $hoy)
        ->where('estado_pago', 'pagado')
        ->count();
    
    $clientes_atendidos = Venta::where('user_id', $user->id)
        ->whereDate('fecha', $hoy)
        ->where('estado_pago', 'pagado')
        ->whereNotNull('cliente_id')
        ->distinct('cliente_id')
        ->count('cliente_id');
    
    // Buscar caja abierta del usuario
    $caja = Caja::where('user_id', $user->id)
        ->where('estado', 'abierta')
        ->first();

    $caja_actual = $caja ? $caja->monto_final : 0;

    // Últimas ventas del día
    $ultimas_ventas = Venta::where('user_id', $user->id)
        ->whereDate('fecha', $hoy)
        ->with('cliente')
        ->orderBy('created_at', 'desc')
        ->limit(10)
        ->get();

    return view('dashboards.tienda', [
        'ventas_dia' => $ventas_dia,
        'caja_actual' => $caja_actual,
        'caja' => $caja,
        'transacciones_dia' => $transacciones_dia,
        'clientes_atendidos' => $clientes_atendidos,
        'ultimas_ventas' => $ultimas_ventas,
    ]);
}
}