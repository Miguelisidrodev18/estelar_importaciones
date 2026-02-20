<?php

namespace App\Http\Controllers;

use App\Models\Imei;
use App\Models\Producto;
use App\Models\Almacen;
use App\Models\Catalogo\Color;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use SimpleSoftwareIO\QrCode\Facades\QrCode; // Instalar: composer require simplesoftwareio/simple-qrcode

class ImeiController extends Controller
{
    /**
     * Constructor - Solo Admin y Almacenero
     */
    public function __construct()
    {
        $this->middleware('role:Administrador,Almacenero');
    }

    /**
     * Mostrar listado de IMEIs
     */
    public function index(Request $request)
    {
        $query = Imei::with(['producto', 'almacen', 'color']);
        
        // Filtro por búsqueda (IMEI o Serie)
        if ($request->filled('buscar')) {
            $query->where(function($q) use ($request) {
                $q->where('codigo_imei', 'like', '%' . $request->buscar . '%')
                    ->orWhere('serie', 'like', '%' . $request->buscar . '%');
            });
        }
        
        // Filtro por producto
        if ($request->filled('producto_id')) {
            $query->where('producto_id', $request->producto_id);
        }
        
        // Filtro por almacén
        if ($request->filled('almacen_id')) {
            $query->where('almacen_id', $request->almacen_id);
        }
        
        // Filtro por estado
        if ($request->filled('estado')) {
            $query->where('estado_imei', $request->estado);
        }

        $imeis = $query->orderBy('created_at', 'desc')->paginate(20);

        // Estadísticas completas
        $stats = [
            'total'       => Imei::count(),
            'disponibles' => Imei::where('estado_imei', 'en_stock')->count(),
            'reservados'  => Imei::where('estado_imei', 'reservado')->count(),
            'vendidos'    => Imei::where('estado_imei', 'vendido')->count(),
            'garantia'    => Imei::where('estado_imei', 'garantia')->count(),
            'devueltos'   => Imei::where('estado_imei', 'devuelto')->count(),
            'reemplazados'=> Imei::where('estado_imei', 'reemplazado')->count(),
        ];

        // Para los filtros
        $productos = Producto::where('tipo_inventario', 'serie')
                    ->with(['marca', 'modelo', 'color'])
                    ->where('estado', 'activo')
                    ->orderBy('nombre')
                    ->get();
        
        $almacenes = Almacen::where('estado', 'activo')
                    ->orderBy('nombre')
                    ->get();
        
        return view('inventario.imeis.index', compact('imeis', 'stats', 'productos', 'almacenes'));
    }

    /**
     * Mostrar formulario para crear IMEI
     */
    public function create(Request $request)
    {
        // Si viene un producto por parámetro, preseleccionarlo
        $productoSeleccionado = null;
        if ($request->filled('producto_id')) {
            $productoSeleccionado = Producto::with(['marca', 'modelo', 'color'])
                                    ->find($request->producto_id);
        }

        // Cargar productos tipo SERIE (celulares) activos
        $productos = Producto::with(['marca', 'modelo', 'color'])
            ->where('tipo_inventario', 'serie')
            ->where('estado', 'activo')
            ->orderBy('nombre')
            ->get()
            ->map(function($producto) {
                // Enriquecer con nombre formateado
                $producto->nombre_formateado = trim(
                    ($producto->marca?->nombre ?? '') . ' ' . 
                    ($producto->modelo?->nombre ?? '') . ' ' . 
                    ($producto->color?->nombre ?? '')
                );
                if (empty($producto->nombre_formateado)) {
                    $producto->nombre_formateado = $producto->nombre;
                }
                return $producto;
            });

        $colores = Color::where('estado', 'activo')
            ->orderBy('nombre')
            ->get();

        $almacenes = Almacen::where('estado', 'activo')
            ->orderBy('nombre')
            ->get();

        return view('inventario.imeis.create', compact('productos', 'colores', 'almacenes', 'productoSeleccionado'));
    }

    /**
     * Guardar nuevo IMEI
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'codigo_imei' => 'required|string|size:15|unique:imeis,codigo_imei',
            'producto_id' => 'required|exists:productos,id',
            'almacen_id'  => 'required|exists:almacenes,id',
            'color_id'    => 'nullable|exists:colores,id',
            'serie'       => 'nullable|string|max:50',
            'estado_imei' => 'required|in:en_stock,vendido,garantia,devuelto,reemplazado,reservado',
        ], [
            'codigo_imei.required' => 'El código IMEI es obligatorio',
            'codigo_imei.size'     => 'El IMEI debe tener exactamente 15 dígitos',
            'codigo_imei.unique'   => 'Este código IMEI ya está registrado en el sistema',
            'producto_id.required' => 'Debe seleccionar un producto',
            'almacen_id.required'  => 'Debe seleccionar un almacén',
        ]);

        // Verificar que el producto sea tipo serie (celular)
        $producto = Producto::findOrFail($validated['producto_id']);
        if ($producto->tipo_inventario !== 'serie') {
            return back()->withErrors(['producto_id' => 'Solo se pueden registrar IMEIs para productos tipo serie/celular']);
        }

        DB::transaction(function () use ($validated, $producto) {
            // Crear IMEI
            $imei = Imei::create([
                'codigo_imei' => $validated['codigo_imei'],
                'producto_id' => $validated['producto_id'],
                'almacen_id'  => $validated['almacen_id'],
                'color_id'    => $validated['color_id'] ?? null,
                'serie'       => $validated['serie'] ?? null,
                'estado_imei' => $validated['estado_imei'],
                'fecha_ingreso' => now(),
                'usuario_registro_id' => auth()->id(),
            ]);
            
            // Solo incrementar stock si está en stock
            if ($validated['estado_imei'] === 'en_stock') {
                // Incrementar stock del producto
                $producto->increment('stock_actual');
                
                // Incrementar stock en almacén
                \App\Models\StockAlmacen::obtenerOCrear($validated['producto_id'], $validated['almacen_id'])
                    ->incrementar(1);
            }
            
            // Generar QR para el IMEI (opcional)
            $this->generarQRParaIMEI($imei);
        });

        return redirect()
            ->route('inventario.imeis.index')
            ->with('success', 'IMEI registrado exitosamente');
    }

    /**
     * Mostrar detalle de un IMEI
     */
    public function show(Imei $imei)
    {
        $imei->load([
        'producto.categoria',
        'producto.marca',
        'producto.modelo',
        'producto.color',
        'almacen',
        'color',
        'usuarioRegistro',
        'movimientos' => function($q) {
            $q->with('usuario')->latest();
        }
    ]);
    
    return view('inventario.imeis.show', compact('imei'));
    }
/**
 * Regenerar QR del IMEI
 */
    public function regenerarQR(Request $request, Imei $imei)
    {
        try {
            // Eliminar QR anterior si existe
            if ($imei->qr_path && \Storage::disk('public')->exists($imei->qr_path)) {
                \Storage::disk('public')->delete($imei->qr_path);
            }

            // Generar nuevo QR usando el método privado existente
            $this->generarQRParaIMEI($imei->fresh());
            
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'qr_url' => Storage::url($path),
                    'message' => 'QR regenerado exitosamente'
                ]);
            }
            
            return back()->with('success', 'QR regenerado exitosamente');
            
        } catch (\Exception $e) {
            \Log::error('Error regenerando QR', [
                'imei_id' => $imei->id,
                'error' => $e->getMessage()
            ]);
            
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al regenerar QR: ' . $e->getMessage()
                ], 500);
            }
            
            return back()->with('error', 'Error al regenerar QR: ' . $e->getMessage());
        }
    }
    /**
     * Mostrar QR del IMEI
     */
    public function mostrarQR(Imei $imei)
    {
        try {
            // Si no tiene QR, generarlo usando el método privado
            if (!$imei->qr_path || !\Storage::disk('public')->exists($imei->qr_path)) {
                $this->generarQRParaIMEI($imei);
                $imei->refresh();
            }

            // Devolver la imagen
            return \Storage::disk('public')->response($imei->qr_path);
            
        } catch (\Exception $e) {
            \Log::error('Error mostrando QR', [
                'imei_id' => $imei->id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json(['error' => 'No se pudo cargar el QR'], 404);
        }
    }
    /**
     * Descargar QR del IMEI
     */
    public function descargarQR(Imei $imei)
    {
        try {
            // Si no tiene QR, generarlo usando el método privado
            if (!$imei->qr_path || !\Storage::disk('public')->exists($imei->qr_path)) {
                $this->generarQRParaIMEI($imei);
                $imei->refresh();
            }

            // Descargar la imagen
            return \Storage::disk('public')->download(
                $imei->qr_path,
                "IMEI_{$imei->codigo_imei}.png"
            );
            
        } catch (\Exception $e) {
            \Log::error('Error descargando QR', [
                'imei_id' => $imei->id,
                'error' => $e->getMessage()
            ]);
            
            return back()->with('error', 'No se pudo descargar el QR');
        }
    }
    /**
     * Mostrar formulario para editar IMEI
     */
    public function edit(Imei $imei)
    {
        $almacenes = Almacen::activos()->orderBy('nombre')->get();
        $colores = Color::where('estado', 'activo')->orderBy('nombre')->get();
        
        return view('inventario.imeis.edit', compact('imei', 'almacenes', 'colores'));
    }

    /**
        * Actualizar IMEI
    */
    public function update(Request $request, Imei $imei)
    {
        $validated = $request->validate([
            'almacen_id' => 'required|exists:almacenes,id',
            'color_id' => 'nullable|exists:colores,id',
            'serie' => 'nullable|string|max:50',
            'estado_imei' => 'required|in:en_stock,reservado,vendido,garantia,devuelto,reemplazado',
            'fecha_garantia' => 'nullable|date',
            'observaciones' => 'nullable|string',
        ]);

        try {
            DB::transaction(function () use ($validated, $imei) {
                $oldEstado = $imei->estado_imei;
                $oldAlmacen = $imei->almacen_id;
                
                // Actualizar IMEI
                $imei->update($validated);
                
                // Registrar movimiento si cambió el estado
                if ($oldEstado !== $validated['estado_imei']) {
                    \App\Models\MovimientoInventario::create([
                        'imei_id' => $imei->id,
                        'producto_id' => $imei->producto_id,
                        'almacen_id' => $imei->almacen_id,
                        'tipo_movimiento' => 'ajuste',
                        'motivo' => "Cambio de estado: " . 
                                str_replace('_', ' ', $oldEstado) . " -> " . 
                                str_replace('_', ' ', $validated['estado_imei']),
                        'usuario_id' => auth()->id(),
                    ]);
                }
                
                // Actualizar stocks si es necesario
                $this->actualizarStocksPorCambio($imei, $oldEstado, $oldAlmacen);
            });

            return redirect()
                ->route('inventario.imeis.show', $imei)
                ->with('success', 'IMEI actualizado exitosamente');

        } catch (\Exception $e) {
            \Log::error('Error actualizando IMEI', [
                'imei_id' => $imei->id,
                'error' => $e->getMessage()
            ]);

            return back()
                ->withInput()
                ->with('error', 'Error al actualizar el IMEI: ' . $e->getMessage());
        }
    }

    /**
     * API: Validar si un IMEI ya existe
     */
    public function validarImei(Request $request)
    {
        $codigo = $request->get('codigo');
        $id = $request->get('id'); // Para edición, excluir este ID
        
        if (strlen($codigo) !== 15 || !ctype_digit($codigo)) {
            return response()->json([
                'valido' => false,
                'mensaje' => 'El IMEI debe tener 15 dígitos numéricos'
            ]);
        }
        
        $query = Imei::where('codigo_imei', $codigo);
        if ($id) {
            $query->where('id', '!=', $id);
        }
        
        $existe = $query->exists();
        
        return response()->json([
            'valido' => !$existe,
            'existe' => $existe,
            'mensaje' => $existe ? 'Este IMEI ya está registrado' : 'IMEI disponible'
        ]);
    }

    /**
     * API: Generar IMEI aleatorio válido
     */
    public function generarImei()
    {
        do {
            // Generar IMEI con algoritmo Luhn
            $imei = $this->generarImeiAleatorio();
            $existe = Imei::where('codigo_imei', $imei)->exists();
        } while ($existe);
        
        return response()->json([
            'success' => true,
            'imei' => $imei,
            'formateado' => $this->formatearIMEI($imei)
        ]);
    }

    /**
     * API: Buscar productos para autocomplete
     */
    public function buscarProductos(Request $request)
    {
        $termino = $request->get('q', '');
        
        $productos = Producto::with(['marca', 'modelo', 'color'])
            ->where('tipo_inventario', 'serie')
            ->where('estado', 'activo')
            ->where(function($q) use ($termino) {
                $q->where('nombre', 'like', "%{$termino}%")
                  ->orWhere('codigo', 'like', "%{$termino}%")
                  ->orWhereHas('marca', function($sq) use ($termino) {
                      $sq->where('nombre', 'like', "%{$termino}%");
                  })
                  ->orWhereHas('modelo', function($sq) use ($termino) {
                      $sq->where('nombre', 'like', "%{$termino}%");
                  });
            })
            ->limit(20)
            ->get()
            ->map(function($producto) {
                return [
                    'id' => $producto->id,
                    'text' => trim(
                        ($producto->marca?->nombre ?? '') . ' ' . 
                        ($producto->modelo?->nombre ?? '') . ' ' . 
                        ($producto->color?->nombre ?? '')
                    ) ?: $producto->nombre,
                    'codigo' => $producto->codigo,
                    'marca' => $producto->marca?->nombre,
                    'modelo' => $producto->modelo?->nombre,
                    'color' => $producto->color?->nombre,
                    'imagen' => $producto->imagen_url
                ];
            });
        
        return response()->json($productos);
    }

    /**
     * API: Generar QR para un IMEI
     */
    public function generarQR(Imei $imei)
    {
        try {
            $data = [
                'imei' => $imei->codigo_imei,
                'producto' => $imei->producto->nombre,
                'marca' => $imei->producto->marca?->nombre,
                'modelo' => $imei->producto->modelo?->nombre,
                'estado' => $imei->estado_imei,
                'url' => route('inventario.imeis.show', $imei)
            ];
            
            $qrCode = QrCode::format('png')
                ->size(300)
                ->margin(1)
                ->errorCorrection('H')
                ->generate(json_encode($data));
            
            return response($qrCode)->header('Content-Type', 'image/png');
            
        } catch (\Exception $e) {
            Log::error('Error generando QR', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Error generando QR'], 500);
        }
    }

    /**
     * API: Obtener IMEIs disponibles por producto y almacén
     */
    public function getImeisDisponibles(Request $request)
    {
        $productoId = $request->get('producto_id');
        $almacenId = $request->get('almacen_id');
        
        $imeis = Imei::with('color')
            ->where('producto_id', $productoId)
            ->where('almacen_id', $almacenId)
            ->where('estado_imei', 'en_stock')
            ->orderBy('codigo_imei')
            ->get(['id', 'codigo_imei', 'serie', 'color_id', 'estado_imei']);
        
        return response()->json($imeis);
    }

    /**
     * Generar IMEI aleatorio válido con algoritmo Luhn
     */
    private function generarImeiAleatorio(): string
    {
        // Generar 14 dígitos aleatorios
        $digitos = [];
        for ($i = 0; $i < 14; $i++) {
            $digitos[] = random_int(0, 9);
        }
        
        // Calcular dígito verificador (algoritmo de Luhn)
        $suma = 0;
        for ($i = 0; $i < 14; $i++) {
            $valor = $digitos[$i];
            if ($i % 2 === 0) { // Posiciones impares (empezando desde 0)
                $valor *= 2;
                if ($valor > 9) {
                    $valor = $valor - 9;
                }
            }
            $suma += $valor;
        }
        
        $digitoVerificador = (10 - ($suma % 10)) % 10;
        $digitos[] = $digitoVerificador;
        
        return implode('', $digitos);
    }

    /**
     * Formatear IMEI para mostrar (XX-XXXXXX-XXXXXX-X)
     */
    private function formatearIMEI(string $imei): string
    {
        if (strlen($imei) !== 15) return $imei;
        
        return substr($imei, 0, 2) . '-' . 
               substr($imei, 2, 6) . '-' . 
               substr($imei, 8, 6) . '-' . 
               substr($imei, 14, 1);
    }

    /**
     * Generar QR para IMEI y guardar referencia
     */
    private function generarQRParaIMEI(Imei $imei): void
    {
        try {
            $data = [
                'imei' => $imei->codigo_imei,
                'id' => $imei->id,
                'producto' => $imei->producto->nombre,
                'fecha' => now()->format('Y-m-d')
            ];
            
            // Guardar QR en storage (opcional)
            $qrCode = QrCode::format('png')
                ->size(200)
                ->generate(json_encode($data));
            
            $path = "qrs/imei_{$imei->id}.png";
            \Storage::disk('public')->put($path, $qrCode);
            
            $imei->update(['qr_path' => $path]);
            
        } catch (\Exception $e) {
            Log::warning('No se pudo generar QR', ['imei_id' => $imei->id, 'error' => $e->getMessage()]);
        }
    }
    /**
     * Generar etiqueta para imprimir
     */
    public function generarEtiqueta(Imei $imei)
    {
            $imei->load(['producto.marca', 'producto.modelo', 'producto.color', 'color']);
    
        $html = view('inventario.imeis.etiqueta', compact('imei'))->render();
    
        return response($html);
    }

    /**
     * Generar etiquetas masivas
     */
    public function generarEtiquetasMasivas(Request $request)
    {
        $request->validate([
            'imeis' => 'required|array',
            'imeis.*' => 'exists:imeis,id'
        ]);

        $imeis = Imei::with(['producto.marca', 'producto.modelo', 'producto.color', 'color'])
            ->whereIn('id', $request->imeis)
            ->get();

        if ($imeis->isEmpty()) {
            return response()->json(['error' => 'No se encontraron IMEIs'], 404);
        }

        $html = view('inventario.imeis.etiquetas-masivas', compact('imeis'))->render();

        return response($html);
    }

    /**
     * Página de impresión del QR de un IMEI
     */
    public function imprimirQR(Imei $imei)
    {
        try {
            if (!$imei->qr_path || !\Storage::disk('public')->exists($imei->qr_path)) {
                $this->generarQRParaIMEI($imei);
                $imei->refresh();
            }

            $qrUrl = \Storage::url($imei->qr_path);

            $html = '<html><head><title>QR IMEI '.$imei->codigo_imei.'</title>'
                . '<style>body{display:flex;justify-content:center;align-items:center;height:100vh;flex-direction:column;font-family:sans-serif}'
                . 'img{width:200px;height:200px} p{font-size:14px;margin:4px 0}'
                . '</style></head><body>'
                . '<img src="'.asset($qrUrl).'" alt="QR">'
                . '<p><strong>IMEI:</strong> '.$imei->codigo_imei.'</p>'
                . '<p>'.($imei->producto->nombre ?? '').'</p>'
                . '<script>window.onload=function(){window.print()}</script>'
                . '</body></html>';

            return response($html);
        } catch (\Exception $e) {
            return response()->json(['error' => 'No se pudo generar el QR para impresión'], 500);
        }
    }
        /**
     * Cambiar estado de IMEI (API)
     */
    public function cambiarEstado(Request $request, Imei $imei)
    {
        $request->validate([
            'estado' => 'required|in:en_stock,reservado,vendido,garantia,devuelto,reemplazado'
        ]);

        try {
            DB::transaction(function() use ($request, $imei) {
                $oldEstado = $imei->estado_imei;
                $imei->update(['estado_imei' => $request->estado]);
                
                // Registrar movimiento
                \App\Models\MovimientoInventario::create([
                    'imei_id' => $imei->id,
                    'producto_id' => $imei->producto_id,
                    'almacen_id' => $imei->almacen_id,
                    'tipo_movimiento' => $request->estado === 'vendido' ? 'salida' : 'ajuste',
                    'motivo' => "Cambio de estado: $oldEstado -> $request->estado",
                    'usuario_id' => auth()->id()
                ]);
            });

            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    /**
     * Actualizar stocks cuando cambia estado/almacén
     */
    private function actualizarStocksPorCambio(Imei $imei, string $oldEstado, ?int $oldAlmacen): void
    {
        // Si el estado cambió de 'en_stock' a otro
        if ($oldEstado === 'en_stock' && $imei->estado_imei !== 'en_stock') {
            $imei->producto->decrement('stock_actual');
            \App\Models\StockAlmacen::obtenerOCrear($imei->producto_id, $oldAlmacen)
                ->decrementar(1);
        }
        
        // Si el estado cambió a 'en_stock' desde otro
        if ($oldEstado !== 'en_stock' && $imei->estado_imei === 'en_stock') {
            $imei->producto->increment('stock_actual');
            \App\Models\StockAlmacen::obtenerOCrear($imei->producto_id, $imei->almacen_id)
                ->incrementar(1);
        }
        
        // Si solo cambió el almacén pero sigue en stock
        if ($imei->estado_imei === 'en_stock' && $oldAlmacen !== $imei->almacen_id) {
            \App\Models\StockAlmacen::obtenerOCrear($imei->producto_id, $oldAlmacen)
                ->decrementar(1);
            \App\Models\StockAlmacen::obtenerOCrear($imei->producto_id, $imei->almacen_id)
                ->incrementar(1);
        }
    }
}