<?php

namespace App\Services;

use App\Models\Producto;
use App\Models\ProductoVariante;
use App\Models\Catalogo\Color;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class VarianteService
{
    /**
     * Obtener o crear variante dado el producto base, color y capacidad.
     */
    public function obtenerOCrearVariante(
        Producto $producto,
        ?int $colorId,
        ?string $capacidad,
        float $sobreprecio = 0
    ): ProductoVariante {
        // Normalizar capacidad
        $capacidad = $capacidad ? trim($capacidad) : null;

        // Buscar variante existente
        $variante = ProductoVariante::where('producto_id', $producto->id)
            ->when($colorId, fn($q) => $q->where('color_id', $colorId), fn($q) => $q->whereNull('color_id'))
            ->when($capacidad, fn($q) => $q->where('capacidad', $capacidad), fn($q) => $q->whereNull('capacidad'))
            ->first();

        if ($variante) {
            return $variante;
        }

        // Crear nueva variante
        $color = $colorId ? Color::find($colorId) : null;
        $sku   = ProductoVariante::generarSku($producto, $color, $capacidad);

        $variante = ProductoVariante::create([
            'producto_id'  => $producto->id,
            'color_id'     => $colorId,
            'capacidad'    => $capacidad,
            'sku'          => $sku,
            'sobreprecio'  => $sobreprecio,
            'stock_actual' => 0,
            'stock_minimo' => $producto->stock_minimo ?? 0,
            'estado'       => 'activo',
            'creado_por'   => auth()->id(),
        ]);

        Log::info('Variante de producto creada', [
            'variante_id' => $variante->id,
            'producto_id' => $producto->id,
            'sku'         => $sku,
            'color_id'    => $colorId,
            'capacidad'   => $capacidad,
        ]);

        return $variante;
    }

    /**
     * Listado de variantes de un producto, para consumo en la UI.
     */
    public function getVariantesParaSelector(Producto $producto, ?int $almacenId = null): array
    {
        return $producto->variantesActivas()
            ->with('color')
            ->get()
            ->map(fn($v) => $this->formatearVariante($v))
            ->toArray();
    }

    /**
     * Formatea una variante para respuesta JSON.
     */
    public function formatearVariante(ProductoVariante $v): array
    {
        return [
            'id'             => $v->id,
            'sku'            => $v->sku,
            'color_id'       => $v->color_id,
            'color_nombre'   => $v->color?->nombre,
            'color_hex'      => $v->color?->codigo_hex,
            'capacidad'      => $v->capacidad,
            'sobreprecio'    => (float) $v->sobreprecio,
            'stock_actual'   => (int) $v->stock_actual,
            'stock_minimo'   => (int) $v->stock_minimo,
            'estado'         => $v->estado,
            'nombre_completo'=> $v->nombre_completo,
            'tiene_stock'    => $v->tieneStock(),
        ];
    }

    /**
     * Actualizar sobreprecio de una variante existente.
     */
    public function actualizarSobreprecio(ProductoVariante $variante, float $sobreprecio): void
    {
        $variante->update(['sobreprecio' => $sobreprecio]);
    }

    /**
     * Desactivar variante (soft-disable).
     */
    public function desactivarVariante(ProductoVariante $variante): void
    {
        $variante->update(['estado' => 'inactivo']);
        $variante->sincronizarStockProductoBase();
    }

    /**
     * Obtener variantes agrupadas por producto para el índice de inventario.
     */
    public function getProductosConVariantes(array $filtros = []): \Illuminate\Pagination\LengthAwarePaginator
    {
        $query = Producto::with(['variantesActivas.color', 'categoria', 'marca', 'modelo'])
            ->where('estado', 'activo');

        if (!empty($filtros['buscar'])) {
            $t = $filtros['buscar'];
            $query->where(function ($q) use ($t) {
                $q->where('nombre', 'like', "%{$t}%")
                  ->orWhere('codigo', 'like', "%{$t}%")
                  ->orWhereHas('variantesActivas', fn($qv) => $qv->where('sku', 'like', "%{$t}%"));
            });
        }

        if (!empty($filtros['categoria_id'])) {
            $query->where('categoria_id', $filtros['categoria_id']);
        }

        return $query->orderBy('nombre')->paginate(15);
    }
}
