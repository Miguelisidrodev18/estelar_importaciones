<?php
// app/Services/PrecioRotativoService.php

namespace App\Services;

use App\Models\Producto;
use App\Models\ProductoPrecio;
use App\Models\Cliente;
use App\Models\Proveedor;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class PrecioRotativoService
{
    /**
     * Obtener el precio vigente para un producto según diferentes criterios
     */
    public function obtenerPrecioVigente(
        Producto $producto, 
        ?Cliente $cliente = null, 
        ?Proveedor $proveedor = null,
        float $cantidad = 1,
        string $tipoPrecio = 'venta_normal'
    ) {
        $query = ProductoPrecio::where('producto_id', $producto->id)
            ->where('activo', true)
            ->where(function($q) {
                $q->whereNull('fecha_inicio')
                  ->orWhere('fecha_inicio', '<=', now());
            })
            ->where(function($q) {
                $q->whereNull('fecha_fin')
                  ->orWhere('fecha_fin', '>=', now());
            });

        // Prioridad 1: Precio específico para este proveedor
        if ($proveedor) {
            $query->where('proveedor_id', $proveedor->id);
        }

        // Prioridad 2: Precio específico para este cliente
        if ($cliente) {
            $query->where('cliente_id', $cliente->id);
        }

        // Prioridad 3: Precio por tipo
        $query->where('tipo_precio', $tipoPrecio);

        // Filtrar por cantidad si aplica
        $query->where(function($q) use ($cantidad) {
            $q->whereNull('cantidad_minima')
              ->orWhere('cantidad_minima', '<=', $cantidad);
        })->where(function($q) use ($cantidad) {
            $q->whereNull('cantidad_maxima')
              ->orWhere('cantidad_maxima', '>=', $cantidad);
        });

        // Ordenar por prioridad (menor número = mayor prioridad)
        $precio = $query->orderBy('prioridad', 'asc')
            ->orderBy('fecha_inicio', 'desc')
            ->first();

        if ($precio) {
            return [
                'precio' => $precio->precio,
                'moneda' => $precio->moneda,
                'tipo' => $precio->tipo_precio,
                'proveedor_id' => $precio->proveedor_id,
                'cliente_id' => $precio->cliente_id,
                'id_precio' => $precio->id
            ];
        }

        // Si no hay precio configurado, usar el precio base del producto
        return [
            'precio' => $producto->precio_venta ?? 0,
            'moneda' => 'PEN',
            'tipo' => 'base',
            'proveedor_id' => null,
            'cliente_id' => null
        ];
    }

    /**
     * Calcular precio con IGV
     */
    public function calcularConIGV(float $precio, bool $incluyeIGV = true)
    {
        if ($incluyeIGV) {
            return [
                'base' => $precio / 1.18,
                'igv' => $precio - ($precio / 1.18),
                'total' => $precio
            ];
        } else {
            return [
                'base' => $precio,
                'igv' => $precio * 0.18,
                'total' => $precio * 1.18
            ];
        }
    }

    /**
     * Actualizar precio después de una compra
     */
    public function actualizarPrecioPorCompra(Producto $producto, Proveedor $proveedor, float $precioCompra, float $margen = 30)
    {
        // Calcular precio de venta sugerido
        $precioVenta = $precioCompra * (1 + $margen/100);

        // Crear o actualizar precio para este proveedor
        $precioExistente = ProductoPrecio::where('producto_id', $producto->id)
            ->where('proveedor_id', $proveedor->id)
            ->where('tipo_precio', 'venta_normal')
            ->where('activo', true)
            ->first();

        if ($precioExistente) {
            // Desactivar precio anterior
            $precioExistente->update(['activo' => false]);
        }

        // Crear nuevo precio vigente
        ProductoPrecio::create([
            'producto_id' => $producto->id,
            'tipo_precio' => 'venta_normal',
            'precio' => round($precioVenta, 2),
            'moneda' => 'PEN',
            'fecha_inicio' => now(),
            'proveedor_id' => $proveedor->id,
            'prioridad' => 10,
            'activo' => true,
            'creado_por' => auth()->id()
        ]);

        // Registrar en historial
        \App\Models\ProductoPrecioHistorial::create([
            'producto_id' => $producto->id,
            'tipo_cambio' => 'compra',
            'precio_anterior' => $producto->precio_venta,
            'precio_nuevo' => round($precioVenta, 2),
            'moneda' => 'PEN',
            'motivo' => 'Actualización por compra a proveedor: ' . $proveedor->nombre,
            'usuario_id' => auth()->id()
        ]);

        // Actualizar precio base del producto
        $producto->update([
            'precio_venta' => round($precioVenta, 2)
        ]);

        return round($precioVenta, 2);
    }
}