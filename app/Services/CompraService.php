<?php

namespace App\Services;

use App\Models\Compra;
use App\Models\DetalleCompra;
use App\Models\StockAlmacen;
use App\Models\MovimientoInventario;
use App\Models\Imei;
use App\Models\Producto;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CompraService
{
    /**
     * Registrar una nueva compra con todos sus detalles
     */
    public function registrarCompra(array $datosCompra, array $detalles): Compra
    {
        return DB::transaction(function () use ($datosCompra, $detalles) {
            
            // 1. Validaciones adicionales antes de crear
            $this->validarDetalles($detalles);
            
            // 2. Crear la compra
            $compra = Compra::create($datosCompra);
            
            $subtotalGeneral = 0;
            
            // 3. Procesar cada detalle
            foreach ($detalles as $detalle) {
                $producto = Producto::findOrFail($detalle['producto_id']);
                
                // Calcular subtotal del detalle con descuento si existe
                $precioConDescuento = $detalle['precio_unitario'];
                if (isset($detalle['descuento']) && $detalle['descuento'] > 0) {
                    $precioConDescuento = $detalle['precio_unitario'] * (1 - $detalle['descuento'] / 100);
                }
                
                $subtotalDetalle = $detalle['cantidad'] * $precioConDescuento;
                $subtotalGeneral += $subtotalDetalle;
                
                // 3.1 Crear detalle de compra
                DetalleCompra::create([
                    'compra_id'       => $compra->id,
                    'producto_id'     => $detalle['producto_id'],
                    'modelo_id'       => $detalle['modelo_id'] ?? null,
                    'color_id'        => $detalle['color_id'] ?? null,
                    'cantidad'        => $detalle['cantidad'],
                    'precio_unitario' => $detalle['precio_unitario'],
                    'descuento'       => $detalle['descuento'] ?? 0,
                    'subtotal'        => $subtotalDetalle,
                ]);
                
                // 3.2 Actualizar stock
                $this->actualizarStock($producto, $compra, $detalle);
                
                // 3.3 Registrar IMEIs si es serie/IMEI
                if ($producto->tipo_inventario === 'serie') {
                    $this->registrarIMEIs($detalle, $producto, $compra);
                }
                
                // 3.4 Registrar código de barras generado si existe
                if (isset($detalle['codigo_barras']) && $detalle['codigo_barras']) {
                    $this->actualizarCodigoBarras($producto, $detalle['codigo_barras']);
                }
                
                // 3.5 Actualizar precio de compra del producto
                $this->actualizarPrecioProducto($producto, $detalle['precio_unitario']);
            }
            
            // 4. Registrar movimiento de caja si aplica
            if ($compra->forma_pago === 'contado' && $compra->estado === 'completado') {
                $this->registrarMovimientoCaja($compra);
            }
            
            // 5. Registrar en log para auditoría
            Log::info('Compra registrada', [
                'compra_id' => $compra->id,
                'user_id' => $compra->user_id,
                'total' => $compra->total,
                'productos' => count($detalles)
            ]);
            
            return $compra->fresh([
                'detalles.producto',
                'proveedor',
                'almacen',
                'usuario',
            ]);
        });
    }
    
    /**
     * Validar detalles antes de procesar
     */
    private function validarDetalles(array $detalles): void
    {
        $productosIds = [];
        
        foreach ($detalles as $detalle) {
            // Verificar producto activo
            $producto = Producto::find($detalle['producto_id']);
            if (!$producto || $producto->estado !== 'activo') {
                throw new \Exception("El producto ID {$detalle['producto_id']} no está activo");
            }
            
            // Validar duplicados
            if (in_array($detalle['producto_id'], $productosIds)) {
                throw new \Exception("El producto {$producto->nombre} está duplicado en el detalle");
            }
            $productosIds[] = $detalle['producto_id'];
            
            // Validar que si es serie/IMEI, tenga IMEIs
            if ($producto->tipo_inventario === 'serie' &&
                (!isset($detalle['imeis']) || count($detalle['imeis']) !== $detalle['cantidad'])) {
                throw new \Exception("El producto {$producto->nombre} requiere {$detalle['cantidad']} IMEI(s)");
            }

            // Validar IMEIs únicos (global)
            if ($producto->tipo_inventario === 'serie' && isset($detalle['imeis'])) {
                $this->validarIMEIsUnicos($detalle['imeis']);
            }
        }
    }
    
    /**
     * Validar que los IMEIs no existan ya en el sistema
     */
    private function validarIMEIsUnicos(array $imeis): void
    {
         // Extraer todos los códigos IMEI
        $codigos = array_column($imeis, 'codigo_imei');
        
        // Buscar existentes en una sola consulta
        $existentes = Imei::whereIn('codigo_imei', $codigos)
            ->get(['codigo_imei', 'producto_id', 'estado_imei']);

        if ($existentes->isNotEmpty()) {
            $mensaje = "Los siguientes IMEI ya están registrados:\n";
            foreach ($existentes as $imei) {
                $producto = Producto::find($imei->producto_id);
                $mensaje .= "- {$imei->codigo_imei} (Producto: {$producto->nombre}, Estado: {$imei->estado_imei})\n";
            }
            throw new \Exception($mensaje);
        }
    }
    
    /**
     * Actualizar stock del producto en el almacén
     */
    private function actualizarStock(Producto $producto, Compra $compra, array $detalle): void
    {
        $stock = StockAlmacen::firstOrCreate(
            [
                'producto_id' => $detalle['producto_id'],
                'almacen_id' => $compra->almacen_id,
            ],
            ['cantidad' => 0]
        );
        
        $stockAnterior = $stock->cantidad;
        $stock->increment('cantidad', $detalle['cantidad']);
        
        // Registrar movimiento de inventario
        MovimientoInventario::create([
            'producto_id' => $detalle['producto_id'],
            'almacen_id' => $compra->almacen_id,
            'user_id' => $compra->user_id,
            'tipo_movimiento' => 'ingreso',
            'cantidad' => $detalle['cantidad'],
            'stock_anterior' => $stockAnterior,
            'stock_nuevo' => $stock->cantidad,
            'numero_factura' => $compra->numero_factura,
            'documento_referencia' => $compra->numero_factura,
            'motivo' => 'Compra #' . $compra->id,
            'estado' => 'completado',
        ]);
        
        // Verificar si hay alerta de stock
        if ($stock->cantidad <= $producto->stock_minimo) {
            Log::warning('Producto con stock mínimo', [
                'producto' => $producto->nombre,
                'stock_actual' => $stock->cantidad,
                'stock_minimo' => $producto->stock_minimo
            ]);
            
            // Aquí podrías disparar un evento o notificación
            // event(new StockBajoEvent($producto, $stock));
        }
    }
    
    /**
     * Registrar IMEIs para productos celulares
     */
    private function registrarIMEIs(array $detalle, Producto $producto, Compra $compra): void
    {
        if (!isset($detalle['imeis']) || !is_array($detalle['imeis'])) {
            return;
        }
        
        foreach ($detalle['imeis'] as $imeiData) {
            Imei::create([
                'codigo_imei' => $imeiData['codigo_imei'],
                'serie'       => $imeiData['serie'] ?? null,
                'color_id'    => $detalle['color_id'] ?? null,
                'producto_id' => $detalle['producto_id'],
                'modelo_id'   => $detalle['modelo_id'] ?? null,
                'detalle_compra_id' => null, // Podrías relacionar con el detalle si quieres
                'almacen_id'  => $compra->almacen_id,
                'compra_id'   => $compra->id,
                'estado_imei' => 'en_stock',
            ]);
        }
    }
    
    /**
     * Actualizar precio de compra del producto
     */
    private function actualizarPrecioProducto(Producto $producto, float $precio): void
    {
        // Guardar historial de precios (opcional)
        // PrecioHistorico::create([...]);
        
        $producto->update([
            'ultimo_costo_compra' => $precio,
            'costo_promedio'      => $precio,
            'fecha_ultima_compra' => now(),
        ]);
    }
    
    /**
     * Actualizar código de barras del producto
     */
    private function actualizarCodigoBarras(Producto $producto, string $codigoBarras): void
    {
        if (empty($producto->codigo_barras)) {
            $producto->update(['codigo_barras' => $codigoBarras]);
        }
    }
    
    /**
     * Registrar movimiento en caja (si la compra es al contado)
     */
    private function registrarMovimientoCaja(Compra $compra): void
    {
        // Verificar si hay una caja abierta
        $cajaService = app(CajaService::class);
        $cajaAbierta = \App\Models\Caja::where('user_id', $compra->user_id)
            ->where('estado', 'abierta')
            ->first();
            
        if ($cajaAbierta) {
            $cajaService->registrarMovimiento(
                $cajaAbierta->id,
                'egreso',
                $compra->total,
                'Compra #' . $compra->id . ' - ' . $compra->proveedor->nombre,
                null, // venta_id
                $compra->id // compra_id
            );
        }
    }
    
    /**
     * Anular una compra (revertir stock y movimientos)
     */
    public function anularCompra(Compra $compra): void
    {
        DB::transaction(function () use ($compra) {
            
            // Verificar que la compra se pueda anular
            if ($compra->estado === 'anulado') {
                throw new \Exception('La compra ya está anulada');
            }
            
            // Revertir stock de cada detalle
            foreach ($compra->detalles as $detalle) {
                $stock = StockAlmacen::where([
                    'producto_id' => $detalle->producto_id,
                    'almacen_id' => $compra->almacen_id,
                ])->first();
                
                if ($stock) {
                    $stockAnterior = $stock->cantidad;
                    $stock->decrement('cantidad', $detalle->cantidad);
                    
                    // Registrar movimiento de anulación
                    MovimientoInventario::create([
                        'producto_id' => $detalle->producto_id,
                        'almacen_id' => $compra->almacen_id,
                        'user_id' => auth()->id(),
                        'tipo_movimiento' => 'salida',
                        'cantidad' => $detalle->cantidad,
                        'stock_anterior' => $stockAnterior,
                        'stock_nuevo' => $stock->cantidad,
                        'numero_factura' => $compra->numero_factura,
                        'documento_referencia' => 'ANUL-' . $compra->id,
                        'motivo' => 'Anulación de compra',
                        'estado' => 'completado',
                    ]);
                }
                
                // Marcar IMEIs como devueltos si existen
                if ($detalle->producto->tipo_inventario === 'serie') {
                    Imei::where('compra_id', $compra->id)
                        ->where('producto_id', $detalle->producto_id)
                        ->update(['estado_imei' => 'devuelto']);
                }
            }
            
            // Actualizar estado de la compra
            $compra->update([
                'estado' => 'anulado',
                'fecha_anulacion' => now(),
            ]);
            
            Log::info('Compra anulada', ['compra_id' => $compra->id]);
        });
    }
    
    /**
     * Eliminar una compra (solo si está pendiente y no tiene movimientos)
     */
    public function eliminarCompra(Compra $compra): void
    {
        DB::transaction(function () use ($compra) {
            
            if ($compra->estado !== 'pendiente') {
                throw new \Exception('Solo se pueden eliminar compras pendientes');
            }
            
            // Eliminar detalles
            $compra->detalles()->delete();
            
            // Eliminar IMEIs asociados
            Imei::where('compra_id', $compra->id)->delete();
            
            // Eliminar la compra
            $compra->delete();
            
            Log::info('Compra eliminada', ['compra_id' => $compra->id]);
        });
    }
    
    /**
     * Obtener estadísticas de compras
     */
    public function getEstadisticas(array $filtros = []): array
    {
        $query = Compra::query();
        
        if (isset($filtros['proveedor_id'])) {
            $query->where('proveedor_id', $filtros['proveedor_id']);
        }
        
        if (isset($filtros['fecha_inicio'])) {
            $query->whereDate('fecha', '>=', $filtros['fecha_inicio']);
        }
        
        if (isset($filtros['fecha_fin'])) {
            $query->whereDate('fecha', '<=', $filtros['fecha_fin']);
        }
        
        return [
            'total_compras' => $query->count(),
            'monto_total' => $query->sum('total'),
            'promedio_compra' => $query->avg('total'),
            'por_proveedor' => $query->selectRaw('proveedor_id, count(*) as total, sum(total) as monto')
                ->groupBy('proveedor_id')
                ->with('proveedor')
                ->get(),
            'por_mes' => $query->selectRaw('DATE_FORMAT(fecha, "%Y-%m") as mes, count(*) as total, sum(total) as monto')
                ->groupBy('mes')
                ->orderBy('mes', 'desc')
                ->get(),
        ];
    }
    /**
 * Procesar IMEI desde archivo Excel/CSV
 */
public function procesarArchivoIMEI($archivo, int $productoId, int $cantidadEsperada): array
{
    $imeis = [];
    $errores = [];
    $linea = 1;
    
    try {
        // Abrir archivo
        $handle = fopen($archivo->getRealPath(), 'r');
        
        while (($data = fgetcsv($handle, 1000, ',')) !== FALSE) {
            $linea++;
            
            // Saltar encabezados si existen
            if ($linea == 2 && preg_match('/imei|código|serial/i', $data[0])) {
                continue;
            }
            
            $codigoImei = trim($data[0] ?? '');
            $serie = trim($data[1] ?? '');
            
            // Validar formato
            if (empty($codigoImei)) {
                $errores[] = "Línea {$linea}: IMEI vacío";
                continue;
            }
            
            if (!preg_match('/^\d{15}$/', $codigoImei)) {
                $errores[] = "Línea {$linea}: IMEI '{$codigoImei}' no tiene 15 dígitos";
                continue;
            }
            
            $imeis[] = [
                'codigo_imei' => $codigoImei,
                'serie' => $serie ?: null,
            ];
        }
        
        fclose($handle);
        
        // Validar cantidad
        if (count($imeis) != $cantidadEsperada) {
            throw new \Exception("El archivo debe contener exactamente {$cantidadEsperada} IMEI(s). Se encontraron " . count($imeis));
        }
        
        // Validar duplicados internos
        $codigos = array_column($imeis, 'codigo_imei');
        if (count($codigos) !== count(array_unique($codigos))) {
            $duplicados = array_diff_assoc($codigos, array_unique($codigos));
            throw new \Exception("Hay IMEI duplicados en el archivo: " . implode(', ', array_unique($duplicados)));
        }
        
        // Validar contra base de datos
        $existentes = Imei::whereIn('codigo_imei', $codigos)->pluck('codigo_imei')->toArray();
        if (!empty($existentes)) {
            throw new \Exception("Los siguientes IMEI ya existen: " . implode(', ', $existentes));
        }
        
    } catch (\Exception $e) {
        throw new \Exception("Error procesando archivo: " . $e->getMessage());
    }
    
    return [
        'success' => empty($errores),
        'imeis' => $imeis,
        'errores' => $errores
    ];
}
}