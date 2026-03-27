<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Almacen;
use App\Models\ConfiguracionPagosSucursal;
use App\Models\SerieComprobante;
use App\Models\Sucursal;
use App\Services\SucursalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class SucursalController extends Controller
{
    public function __construct(protected SucursalService $sucursalService)
    {
        $this->middleware('role:Administrador');
    }

    // ── HU-02: Listado ─────────────────────────────────────────────────────────

    public function index()
    {
        $sucursales = Sucursal::with(['almacen', 'series' => fn($q) => $q->where('activo', true)])
            ->orderBy('codigo')
            ->get();

        return view('admin.sucursales.index', compact('sucursales'));
    }

    // ── HU-02: Crear ───────────────────────────────────────────────────────────

    public function create()
    {
        $almacenes = Almacen::where('estado', 'activo')->orderBy('nombre')->get();
        return view('admin.sucursales.create', compact('almacenes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre'        => 'required|string|max:150',
            'direccion'     => 'nullable|string|max:300',
            'departamento'  => 'nullable|string|max:100',
            'provincia'     => 'nullable|string|max:100',
            'distrito'      => 'nullable|string|max:100',
            'ubigeo'        => 'nullable|string|max:6',
            'telefono'      => 'nullable|string|max:20',
            'email'         => 'nullable|email|max:150',
            'es_principal'  => 'boolean',
            'estado'        => 'required|in:activo,inactivo',
        ]);

        $sucursal = $this->sucursalService->crear($validated);

        return redirect()->route('admin.sucursales.edit', $sucursal)
            ->with('success', "Sucursal {$sucursal->codigo} creada. Se generaron las series de comprobantes automáticamente.");
    }

    // ── HU-02/03/04: Editar ────────────────────────────────────────────────────

    public function edit(Sucursal $sucursal)
    {
        $sucursal->load([
            'almacen',
            'series' => fn($q) => $q->orderBy('tipo_comprobante'),
            'pagos',
        ]);
        $almacenes    = Almacen::where('estado', 'activo')->orderBy('nombre')->get();
        $tiposPagos   = ['yape', 'plin', 'transferencia', 'pos'];
        $pagosIndexed = $sucursal->pagos->keyBy('tipo_pago');

        return view('admin.sucursales.edit', compact('sucursal', 'almacenes', 'tiposPagos', 'pagosIndexed'));
    }

    public function update(Request $request, Sucursal $sucursal)
    {
        $validated = $request->validate([
            'nombre'        => 'required|string|max:150',
            'direccion'     => 'nullable|string|max:300',
            'departamento'  => 'nullable|string|max:100',
            'provincia'     => 'nullable|string|max:100',
            'distrito'      => 'nullable|string|max:100',
            'ubigeo'        => 'nullable|string|max:6',
            'telefono'      => 'nullable|string|max:20',
            'email'         => 'nullable|email|max:150',
            'almacen_id'    => 'nullable|exists:almacenes,id',
            'es_principal'  => 'boolean',
            'estado'        => 'required|in:activo,inactivo',
        ]);

        $sucursal->update($validated);

        return redirect()->route('admin.sucursales.edit', $sucursal)
            ->with('success', 'Sucursal actualizada correctamente.');
    }

    public function destroy(Sucursal $sucursal)
    {
        if ($sucursal->es_principal) {
            return back()->with('error', 'No se puede eliminar la sucursal principal.');
        }
        $sucursal->delete();
        return redirect()->route('admin.sucursales.index')
            ->with('success', 'Sucursal eliminada.');
    }

    // ── HU-03: Series de comprobantes ──────────────────────────────────────────

    public function updateSerie(Request $request, Sucursal $sucursal, SerieComprobante $serie)
    {
        $validated = $request->validate([
            'serie'              => 'required|string|max:5',
            'correlativo_actual' => 'required|integer|min:1',
            'formato_impresion'  => 'required|in:A4,ticket,A5',
            'activo'             => 'boolean',
        ]);

        $serie->update($validated);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'serie' => $serie->fresh()]);
        }

        return redirect()->route('admin.sucursales.edit', $sucursal)
            ->with('success', "Serie {$serie->serie} actualizada.");
    }

    public function storeSerie(Request $request, Sucursal $sucursal)
    {
        $validated = $request->validate([
            'tipo_comprobante'   => 'required|string|max:5',
            'tipo_nombre'        => 'required|string|max:80',
            'serie'              => 'required|string|max:5|unique:series_comprobantes,serie,NULL,id,sucursal_id,' . $sucursal->id,
            'correlativo_actual' => 'required|integer|min:1',
            'formato_impresion'  => 'required|in:A4,ticket,A5',
        ]);

        $serie = $sucursal->series()->create($validated + ['activo' => true]);

        return redirect()->route('admin.sucursales.edit', $sucursal)
            ->with('success', "Serie {$serie->serie} agregada.");
    }

    // ── HU-04: Pagos digitales ─────────────────────────────────────────────────

    public function updatePagos(Request $request, Sucursal $sucursal)
    {
        $validated = $request->validate([
            'pagos'                       => 'nullable|array',
            'pagos.*.tipo_pago'           => 'required|in:yape,plin,transferencia,pos',
            'pagos.*.titular'             => 'nullable|string|max:150',
            'pagos.*.numero'              => 'nullable|string|max:20',
            'pagos.*.banco'               => 'nullable|string|max:100',
            'pagos.*.numero_cuenta'       => 'nullable|string|max:50',
            'pagos.*.cci'                 => 'nullable|string|max:30',
            'pagos.*.activo'              => 'nullable|boolean',
            'pagos.*.qr'                  => 'nullable|image|max:2048',
        ]);

        DB::transaction(function () use ($request, $sucursal) {
            foreach (['yape', 'plin', 'transferencia', 'pos'] as $tipo) {
                $datos = $request->input("pagos.{$tipo}", []);
                $activo = $request->boolean("pagos.{$tipo}.activo");

                $pago = ConfiguracionPagosSucursal::firstOrNew([
                    'sucursal_id' => $sucursal->id,
                    'tipo_pago'   => $tipo,
                ]);

                $pago->fill([
                    'titular'        => $datos['titular'] ?? null,
                    'numero'         => $datos['numero'] ?? null,
                    'banco'          => $datos['banco'] ?? null,
                    'numero_cuenta'  => $datos['numero_cuenta'] ?? null,
                    'cci'            => $datos['cci'] ?? null,
                    'activo'         => $activo,
                ]);

                // QR upload
                $qrKey = "pagos.{$tipo}.qr";
                if ($request->hasFile($qrKey)) {
                    if ($pago->qr_imagen_path) Storage::disk('public')->delete($pago->qr_imagen_path);
                    $pago->qr_imagen_path = $request->file($qrKey)->store("qr/{$sucursal->codigo}", 'public');
                }

                $pago->save();
            }
        });

        return redirect()->route('admin.sucursales.edit', $sucursal)
            ->with('success', 'Configuración de pagos guardada.');
    }

    // ── HU-03: Generar series estándar faltantes ───────────────────────────────

    public function generarSeries(Sucursal $sucursal)
    {
        $numSucursal = (int) substr($sucursal->codigo, 1);
        $this->sucursalService->generarSeriesEstandar($sucursal, $numSucursal);

        return redirect()->route('admin.sucursales.edit', $sucursal)
            ->with('success', 'Series estándar generadas (se omitieron las que ya existían).')
            ->with('_tab', 'series');
    }

    // ── HU-05: Comprobantes emitidos ───────────────────────────────────────────

    public function comprobantes(Request $request, Sucursal $sucursal)
    {
        // Almacenes vinculados a esta sucursal (su propio almacén + almacenes sin sucursal asignada)
        $almacenesConOtraSucursal = \App\Models\Sucursal::whereNotNull('almacen_id')
            ->where('id', '!=', $sucursal->id)
            ->pluck('almacen_id');

        $query = \App\Models\Venta::with(['cliente', 'serieComprobante'])
            ->where(function ($q) use ($sucursal, $almacenesConOtraSucursal) {
                $q->where('sucursal_id', $sucursal->id)
                  ->orWhere(function ($q2) use ($sucursal, $almacenesConOtraSucursal) {
                      $q2->whereNull('sucursal_id')
                         ->whereNotIn('almacen_id', $almacenesConOtraSucursal);
                  });
            })
            ->whereIn('tipo_comprobante', ['boleta', 'factura', 'nota_credito'])
            ->orderByDesc('fecha')
            ->orderByDesc('id');

        // Filtros opcionales
        if ($request->filled('tipo')) {
            $query->where('tipo_comprobante', $request->tipo);
        }
        if ($request->filled('estado')) {
            $query->where('estado_pago', $request->estado);
        }
        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($sub) use ($q) {
                $sub->where('codigo', 'like', "%{$q}%")
                    ->orWhereHas('cliente', fn($c) => $c->where('nombre', 'like', "%{$q}%")
                        ->orWhere('numero_documento', 'like', "%{$q}%"));
            });
        }

        $ventas = $query->paginate(30)->withQueryString();

        $baseQuery = fn() => \App\Models\Venta::where(function ($q) use ($sucursal, $almacenesConOtraSucursal) {
            $q->where('sucursal_id', $sucursal->id)
              ->orWhere(fn($q2) => $q2->whereNull('sucursal_id')->whereNotIn('almacen_id', $almacenesConOtraSucursal));
        });

        $totales = [
            'total'   => $baseQuery()->whereIn('tipo_comprobante', ['boleta','factura','nota_credito'])->count(),
            'boleta'  => $baseQuery()->where('tipo_comprobante','boleta')->count(),
            'factura' => $baseQuery()->where('tipo_comprobante','factura')->count(),
        ];

        return view('admin.sucursales.comprobantes', compact('sucursal', 'ventas', 'totales'));
    }
}
