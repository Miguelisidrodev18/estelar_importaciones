<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Venta;
use App\Models\Producto;
use App\Models\Cliente;
use App\Models\Almacen;
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
    
    $caja_actual = 0;
    
    // Stats array (si lo necesitas para otras partes)
    $stats = [
        'ventas_hoy' => $ventas_dia,
        'transacciones_hoy' => $transacciones_dia,
        'clientes_atendidos' => $clientes_atendidos,
        'productos_disponibles' => \App\Models\Producto::activos()->count(),
    ];
    
    $productosPopulares = \App\Models\Producto::activos()
        ->orderBy('nombre')
        ->limit(5)
        ->get();
    
    // PASAR TODO lo que necesita la vista
    return view('dashboards.tienda', [
        // Variables directas que usa la vista
        'ventas_dia' => $ventas_dia,
        'caja_actual' => $caja_actual,
        'transacciones_dia' => $transacciones_dia,
        'clientes_atendidos' => $clientes_atendidos,
        
        // Variables adicionales que podrías necesitar
        'stats' => $stats,
        'productosPopulares' => $productosPopulares,
        'ultimas_ventas' => collect([]), // Vacío por ahora
        'ventas_externas_pendientes' => collect([]), // Vacío por ahora
    ]);
}
}