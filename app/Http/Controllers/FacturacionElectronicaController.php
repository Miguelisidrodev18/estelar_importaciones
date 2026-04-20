<?php

namespace App\Http\Controllers;

use App\Models\Venta;
use App\Models\SerieComprobante;
use App\Models\Sucursal;
use Illuminate\Http\Request;

class FacturacionElectronicaController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:Administrador');
    }

    public function index(Request $request)
    {
        $query = Venta::with(['cliente', 'serieComprobante', 'sucursal'])
            ->whereNotIn('tipo_comprobante', ['cotizacion'])
            ->orderByDesc('fecha');

        if ($request->filled('estado_sunat')) {
            $query->where('estado_sunat', $request->estado_sunat);
        }

        if ($request->filled('tipo_comprobante')) {
            $query->where('tipo_comprobante', $request->tipo_comprobante);
        }

        if ($request->filled('sucursal_id')) {
            $query->where('sucursal_id', $request->sucursal_id);
        }

        if ($request->filled('fecha_desde')) {
            $query->whereDate('fecha', '>=', $request->fecha_desde);
        }

        if ($request->filled('fecha_hasta')) {
            $query->whereDate('fecha', '<=', $request->fecha_hasta);
        }

        if ($request->filled('buscar')) {
            $buscar = $request->buscar;
            $query->where(function ($q) use ($buscar) {
                $q->whereHas('cliente', fn($c) => $c->where('nombre', 'like', "%$buscar%")
                    ->orWhere('documento', 'like', "%$buscar%"))
                  ->orWhereHas('serieComprobante', fn($s) => $s->where('serie', 'like', "%$buscar%"));
            });
        }

        $comprobantes = $query->paginate(20)->withQueryString();

        $sucursales = Sucursal::orderBy('nombre')->get(['id', 'nombre']);

        $stats = [
            'total_emitidos'    => Venta::whereNotIn('tipo_comprobante', ['cotizacion'])->count(),
            'pendiente_envio'   => Venta::where('estado_sunat', 'pendiente_envio')->count(),
            'aceptados'         => Venta::where('estado_sunat', 'aceptado')->count(),
            'rechazados'        => Venta::where('estado_sunat', 'rechazado')->count(),
            'hoy'               => Venta::whereNotIn('tipo_comprobante', ['cotizacion'])
                                       ->whereDate('fecha', today())->count(),
        ];

        return view('facturacion.index', compact('comprobantes', 'sucursales', 'stats'));
    }

    public function series(Request $request)
    {
        $sucursales = Sucursal::orderBy('nombre')->get(['id', 'nombre']);

        $query = SerieComprobante::with('sucursal')->orderBy('sucursal_id')->orderBy('tipo_comprobante');

        if ($request->filled('sucursal_id')) {
            $query->where('sucursal_id', $request->sucursal_id);
        }

        $series = $query->get();

        $tiposComprobante = SerieComprobante::TIPOS;

        return view('facturacion.series', compact('series', 'sucursales', 'tiposComprobante'));
    }

    public function storeSerie(Request $request)
    {
        $request->validate([
            'sucursal_id'       => 'required|exists:sucursales,id',
            'tipo_comprobante'  => 'required|string|max:5',
            'tipo_nombre'       => 'required|string|max:80',
            'serie'             => 'required|string|max:5',
            'correlativo_actual'=> 'required|integer|min:1',
            'formato_impresion' => 'required|in:A4,ticket,A5',
        ]);

        $existe = SerieComprobante::where('sucursal_id', $request->sucursal_id)
            ->where('serie', strtoupper($request->serie))
            ->exists();

        if ($existe) {
            return back()->with('error', 'Ya existe una serie con ese código para esa sucursal.');
        }

        SerieComprobante::create([
            'sucursal_id'        => $request->sucursal_id,
            'tipo_comprobante'   => $request->tipo_comprobante,
            'tipo_nombre'        => $request->tipo_nombre,
            'serie'              => strtoupper($request->serie),
            'correlativo_actual' => $request->correlativo_actual,
            'formato_impresion'  => $request->formato_impresion,
            'activo'             => true,
        ]);

        return back()->with('success', 'Serie creada exitosamente.');
    }

    public function updateSerie(Request $request, SerieComprobante $serie)
    {
        $request->validate([
            'tipo_nombre'        => 'required|string|max:80',
            'correlativo_actual' => 'required|integer|min:1',
            'formato_impresion'  => 'required|in:A4,ticket,A5',
            'activo'             => 'boolean',
        ]);

        $serie->update([
            'tipo_nombre'        => $request->tipo_nombre,
            'correlativo_actual' => $request->correlativo_actual,
            'formato_impresion'  => $request->formato_impresion,
            'activo'             => $request->boolean('activo', true),
        ]);

        return back()->with('success', 'Serie actualizada correctamente.');
    }

    public function destroySerie(SerieComprobante $serie)
    {
        if ($serie->ventas()->exists()) {
            return back()->with('error', 'No se puede eliminar: tiene comprobantes emitidos.');
        }

        $serie->delete();
        return back()->with('success', 'Serie eliminada.');
    }

    public function reenviar(Venta $venta)
    {
        if (!in_array($venta->estado_sunat, ['pendiente_envio', 'rechazado'])) {
            return back()->with('error', 'Este comprobante no puede ser reenviado en su estado actual.');
        }

        $venta->update(['estado_sunat' => 'pendiente_envio']);

        return back()->with('success', 'Comprobante marcado para reenvío a SUNAT.');
    }

    public function configuracion()
    {
        $sucursales = Sucursal::with('series')->orderBy('nombre')->get();

        return view('facturacion.configuracion', compact('sucursales'));
    }
}
