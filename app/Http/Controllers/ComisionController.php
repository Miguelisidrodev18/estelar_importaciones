<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\ComisionDetalleVenta;
use App\Models\ComisionRegla;
use App\Models\Producto;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ComisionController extends Controller
{
    public function index()
    {
        $reglas     = ComisionRegla::with(['usuario', 'categoria', 'producto'])->latest()->get();
        $vendedores = User::whereHas('role', fn($q) => $q->whereIn('nombre', ['Vendedor', 'Tienda', 'Cajero']))->orderBy('name')->get();
        $categorias = Categoria::orderBy('nombre')->get();
        $productos  = Producto::where('estado', 'activo')->orderBy('nombre')->get(['id', 'nombre', 'codigo']);

        // Summary: total pending commissions
        $totalPendiente = ComisionDetalleVenta::where('estado', 'pendiente')->sum('monto_comision');

        return view('comisiones.index', compact('reglas', 'vendedores', 'categorias', 'productos', 'totalPendiente'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre'           => 'required|string|max:100',
            'tipo_aplicacion'  => 'required|in:usuario,categoria,producto',
            'tipo_calculo'     => 'required|in:porcentaje,monto_fijo',
            'valor'            => 'required|numeric|min:0.0001',
            'user_id'          => 'nullable|required_if:tipo_aplicacion,usuario|exists:users,id',
            'categoria_id'     => 'nullable|required_if:tipo_aplicacion,categoria|exists:categorias,id',
            'producto_id'      => 'nullable|required_if:tipo_aplicacion,producto|exists:productos,id',
        ]);

        ComisionRegla::create([
            'nombre'          => $request->nombre,
            'tipo_aplicacion' => $request->tipo_aplicacion,
            'tipo_calculo'    => $request->tipo_calculo,
            'valor'           => $request->valor,
            'user_id'         => $request->tipo_aplicacion === 'usuario' ? $request->user_id : null,
            'categoria_id'    => $request->tipo_aplicacion === 'categoria' ? $request->categoria_id : null,
            'producto_id'     => $request->tipo_aplicacion === 'producto' ? $request->producto_id : null,
            'activo'          => true,
        ]);

        return back()->with('success', 'Regla de comisión creada correctamente.');
    }

    public function update(Request $request, ComisionRegla $regla)
    {
        $request->validate([
            'nombre'       => 'required|string|max:100',
            'tipo_calculo' => 'required|in:porcentaje,monto_fijo',
            'valor'        => 'required|numeric|min:0.0001',
        ]);

        $regla->update($request->only('nombre', 'tipo_calculo', 'valor'));

        return back()->with('success', 'Regla actualizada correctamente.');
    }

    public function destroy(ComisionRegla $regla)
    {
        // Check if there are linked commissions
        if ($regla->id && ComisionDetalleVenta::where('regla_id', $regla->id)->exists()) {
            return back()->with('error', 'No se puede eliminar: hay comisiones generadas con esta regla. Desactívala en su lugar.');
        }
        $regla->delete();
        return back()->with('success', 'Regla eliminada.');
    }

    public function toggle(ComisionRegla $regla)
    {
        $regla->update(['activo' => !$regla->activo]);
        return back()->with('success', $regla->activo ? 'Regla activada.' : 'Regla desactivada.');
    }

    public function reporte(Request $request)
    {
        $vendedores = User::whereHas('role', fn($q) => $q->whereIn('nombre', ['Vendedor', 'Tienda', 'Cajero']))->orderBy('name')->get();

        $query = ComisionDetalleVenta::with(['vendedor', 'detalleVenta.venta', 'detalleVenta.producto', 'regla']);

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }
        if ($request->filled('fecha_desde')) {
            $query->whereHas('detalleVenta.venta', fn($q) => $q->whereDate('fecha', '>=', $request->fecha_desde));
        }
        if ($request->filled('fecha_hasta')) {
            $query->whereHas('detalleVenta.venta', fn($q) => $q->whereDate('fecha', '<=', $request->fecha_hasta));
        }

        $comisiones = $query->latest()->paginate(30)->withQueryString();

        // Summary per seller
        $resumen = ComisionDetalleVenta::with('vendedor')
            ->when($request->filled('estado'), fn($q) => $q->where('estado', $request->estado))
            ->when($request->filled('user_id'), fn($q) => $q->where('user_id', $request->user_id))
            ->selectRaw('user_id, SUM(monto_comision) as total, COUNT(*) as cantidad, SUM(CASE WHEN estado="pagado" THEN monto_comision ELSE 0 END) as pagado, SUM(CASE WHEN estado="pendiente" THEN monto_comision ELSE 0 END) as pendiente')
            ->groupBy('user_id')
            ->get();

        return view('comisiones.reporte', compact('comisiones', 'resumen', 'vendedores'));
    }

    public function marcarPagado(Request $request)
    {
        $request->validate(['ids' => 'required|array', 'ids.*' => 'exists:comision_detalle_venta,id']);

        ComisionDetalleVenta::whereIn('id', $request->ids)
            ->where('estado', 'pendiente')
            ->update([
                'estado'             => 'pagado',
                'fecha_pago'         => now()->toDateString(),
                'pagado_por_user_id' => auth()->id(),
            ]);

        return back()->with('success', count($request->ids) . ' comisiones marcadas como pagadas.');
    }
}
