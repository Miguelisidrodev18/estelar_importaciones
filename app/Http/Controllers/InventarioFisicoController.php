<?php

namespace App\Http\Controllers;

use App\Models\Almacen;
use App\Models\Categoria;
use App\Models\ConteoDetalle;
use App\Models\ConteoInventario;
use App\Models\Imei;
use App\Models\Producto;
use App\Models\StockAlmacen;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventarioFisicoController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = ConteoInventario::with(['almacen', 'usuario'])
            ->withCount('detalles')
            ->withCount(['detalles as contados_count' => fn($q) => $q->whereNotNull('stock_fisico')]);

        if ($user->role->nombre !== 'Administrador' && $user->almacen_id) {
            $query->where('almacen_id', $user->almacen_id);
        }

        $conteos = $query->latest()->paginate(20);
        return view('inventario.conteo.index', compact('conteos'));
    }

    public function create()
    {
        $almacenes = Almacen::where('estado', 'activo')->orderBy('nombre')->get();
        return view('inventario.conteo.create', compact('almacenes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre'     => 'required|string|max:100',
            'almacen_id' => 'required|exists:almacenes,id',
        ]);

        $conteo = ConteoInventario::create([
            'nombre'     => $request->nombre,
            'almacen_id' => $request->almacen_id,
            'user_id'    => auth()->id(),
            'estado'     => 'activo',
        ]);

        $this->poblarDetalles($conteo);

        return redirect()->route('inventario-fisico.show', $conteo)
            ->with('success', 'Conteo creado. ¡Puedes comenzar a ingresar el stock físico!');
    }

    public function show(Request $request, ConteoInventario $conteo)
    {
        $categorias = Categoria::orderBy('nombre')->get();

        $query = $conteo->detalles()
            ->with(['producto.categoria', 'variante'])
            ->join('productos', 'conteo_detalles.producto_id', '=', 'productos.id')
            ->select('conteo_detalles.*');

        if ($request->filled('categoria_id')) {
            $query->where('productos.categoria_id', $request->categoria_id);
        }

        if ($request->filled('buscar')) {
            $buscar = '%' . $request->buscar . '%';
            $query->where(function ($q) use ($buscar) {
                $q->where('productos.nombre', 'like', $buscar)
                  ->orWhere('productos.codigo', 'like', $buscar);
            });
        }

        if ($request->boolean('solo_faltantes')) {
            $query->whereNotNull('conteo_detalles.stock_fisico')
                  ->whereRaw('conteo_detalles.stock_fisico < conteo_detalles.stock_sistema');
        }

        $detalles = $query->orderBy('productos.nombre')->paginate(50)->withQueryString();

        // KPI stats
        $stats = $conteo->detalles()
            ->selectRaw('
                COUNT(*) as total_lineas,
                COUNT(stock_fisico) as contados,
                SUM(CASE WHEN stock_fisico IS NOT NULL AND stock_fisico < stock_sistema THEN (stock_sistema - stock_fisico) ELSE 0 END) as total_faltante_unidades
            ')
            ->first();

        // Join productos for valor calculations
        $valorStats = $conteo->detalles()
            ->join('productos', 'conteo_detalles.producto_id', '=', 'productos.id')
            ->whereNotNull('conteo_detalles.stock_fisico')
            ->whereRaw('conteo_detalles.stock_fisico < conteo_detalles.stock_sistema')
            ->selectRaw('
                SUM((conteo_detalles.stock_sistema - conteo_detalles.stock_fisico) * COALESCE(productos.costo_promedio, 0)) as valor_compra,
                SUM((conteo_detalles.stock_sistema - conteo_detalles.stock_fisico) * COALESCE(productos.ultimo_costo_compra, 0)) as valor_venta
            ')
            ->first();

        return view('inventario.conteo.show', compact(
            'conteo', 'detalles', 'categorias', 'stats', 'valorStats'
        ));
    }

    public function updateDetalle(Request $request, ConteoInventario $conteo, ConteoDetalle $detalle): JsonResponse
    {
        if ($detalle->conteo_id !== $conteo->id) {
            return response()->json(['ok' => false, 'message' => 'No autorizado'], 403);
        }

        $request->validate([
            'stock_fisico'  => 'nullable|integer|min:0',
            'observaciones' => 'nullable|string|max:255',
        ]);

        $data = ['observaciones' => $request->observaciones];
        if ($request->filled('stock_fisico') || $request->input('stock_fisico') === '0') {
            $data['stock_fisico'] = (int) $request->stock_fisico;
            $data['contado_at']   = now();
        }

        $detalle->update($data);

        $diferencia = $detalle->diferencia;
        $faltante   = $detalle->faltante;

        return response()->json([
            'ok'           => true,
            'diferencia'   => $diferencia,
            'faltante'     => $faltante,
            'valor_faltante' => number_format($detalle->valor_faltante, 2),
            'contado_at'   => $detalle->contado_at?->format('d/m H:i'),
        ]);
    }

    public function reiniciar(ConteoInventario $conteo)
    {
        $conteo->detalles()->update([
            'stock_fisico'  => null,
            'contado_at'    => null,
            'observaciones' => null,
        ]);

        return back()->with('success', 'Conteo reiniciado correctamente.');
    }

    public function exportPdf(ConteoInventario $conteo)
    {
        $conteo->load(['almacen', 'usuario']);
        $detalles = $conteo->detalles()
            ->with(['producto.categoria', 'variante'])
            ->join('productos', 'conteo_detalles.producto_id', '=', 'productos.id')
            ->select('conteo_detalles.*')
            ->orderBy('productos.nombre')
            ->get();

        $pdf = Pdf::loadView('pdf.conteo-inventario', compact('conteo', 'detalles'))
            ->setPaper('a4', 'landscape');

        return $pdf->download('conteo-' . $conteo->id . '-' . now()->format('Y-m-d') . '.pdf');
    }

    public function exportExcel(ConteoInventario $conteo)
    {
        $conteo->load(['almacen']);
        $detalles = $conteo->detalles()
            ->with(['producto.categoria', 'variante'])
            ->join('productos', 'conteo_detalles.producto_id', '=', 'productos.id')
            ->select('conteo_detalles.*')
            ->orderBy('productos.nombre')
            ->get();

        $filename = 'conteo-' . $conteo->id . '-' . now()->format('Y-m-d') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($conteo, $detalles) {
            $handle = fopen('php://output', 'w');
            // BOM UTF-8
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($handle, [
                'Código', 'Producto', 'Variante', 'Categoría',
                'Mín.', 'Stock Sistema', 'Stock Físico', 'Diferencia',
                'P. Costo', 'Valor Faltante', 'Observaciones',
            ]);

            foreach ($detalles as $d) {
                fputcsv($handle, [
                    $d->producto->codigo,
                    $d->producto->nombre,
                    $d->variante?->nombre_completo ?? '—',
                    $d->producto->categoria?->nombre ?? '—',
                    $d->producto->stock_minimo,
                    $d->stock_sistema,
                    $d->stock_fisico ?? '—',
                    $d->diferencia ?? '—',
                    number_format($d->producto->costo_promedio ?? 0, 2),
                    number_format($d->valor_faltante, 2),
                    $d->observaciones ?? '',
                ]);
            }
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function poblarDetalles(ConteoInventario $conteo): void
    {
        $almacenId = $conteo->almacen_id;
        $rows = [];

        // Accesorios / cantidad: from StockAlmacen, one row per product (no variant breakdown at StockAlmacen level)
        $stocksAccesorio = StockAlmacen::where('almacen_id', $almacenId)
            ->with('producto.variantesActivas')
            ->get();

        foreach ($stocksAccesorio as $sa) {
            $prod = $sa->producto;
            if (!$prod || $prod->tipo_inventario === 'serie') {
                continue;
            }

            if ($prod->variantesActivas->isNotEmpty()) {
                foreach ($prod->variantesActivas as $variante) {
                    $rows[] = [
                        'conteo_id'     => $conteo->id,
                        'producto_id'   => $prod->id,
                        'variante_id'   => $variante->id,
                        'stock_sistema' => max(0, (int) $variante->stock_actual),
                        'created_at'    => now(),
                        'updated_at'    => now(),
                    ];
                }
            } else {
                $rows[] = [
                    'conteo_id'     => $conteo->id,
                    'producto_id'   => $prod->id,
                    'variante_id'   => null,
                    'stock_sistema' => max(0, (int) $sa->cantidad),
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ];
            }
        }

        // Serie products: count IMEIs en_stock per product (per variant if variants exist)
        $imeiCounts = Imei::where('almacen_id', $almacenId)
            ->where('estado_imei', Imei::ESTADO_EN_STOCK)
            ->selectRaw('producto_id, variante_id, COUNT(*) as cnt')
            ->groupBy('producto_id', 'variante_id')
            ->get();

        foreach ($imeiCounts as $ic) {
            $rows[] = [
                'conteo_id'     => $conteo->id,
                'producto_id'   => $ic->producto_id,
                'variante_id'   => $ic->variante_id,
                'stock_sistema' => (int) $ic->cnt,
                'created_at'    => now(),
                'updated_at'    => now(),
            ];
        }

        // Bulk insert in chunks to avoid memory issues
        foreach (array_chunk($rows, 200) as $chunk) {
            ConteoDetalle::insert($chunk);
        }
    }
}
