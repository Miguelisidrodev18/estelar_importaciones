<?php

namespace App\Http\Controllers;

use App\Models\User;
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
        $data = [
            'ventas_hoy' => 0, // Placeholder - se implementará en el módulo de ventas
            'ventas_mes' => 0,
            'clientes_atendidos' => 0,
            'productos_vendidos' => 0,
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
    
    // Estadísticas básicas para tienda
    $stats = [
        'ventas_hoy' => 0, // Implementar cuando tengamos módulo de ventas
        'transacciones_hoy' => 0,
        'clientes_atendidos' => 0,
        'productos_disponibles' => \App\Models\Producto::activos()->count(),
    ];
    
    // Productos más vendidos (placeholder)
    $productosPopulares = \App\Models\Producto::activos()
        ->orderBy('nombre')
        ->limit(5)
        ->get();
    
    return view('dashboards.tienda', compact('stats', 'productosPopulares'));
}

}