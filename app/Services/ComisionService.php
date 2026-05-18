<?php

namespace App\Services;

use App\Models\Venta;
use App\Models\ComisionRegla;
use App\Models\ComisionDetalleVenta;
use Illuminate\Support\Facades\DB;

class ComisionService
{
    /**
     * Calcula y persiste comisiones para todos los detalles de una venta.
     * Prioridad de regla: producto > categoría > usuario.
     */
    public function calcularParaVenta(Venta $venta): void
    {
        $vendedorId = $venta->user_id;
        if (!$vendedorId) {
            return;
        }

        foreach ($venta->detalles as $detalle) {
            $productoId  = $detalle->producto_id;
            $categoriaId = $detalle->producto?->categoria_id;

            $regla = $this->resolverRegla($vendedorId, $productoId, $categoriaId);
            if (!$regla) {
                continue;
            }

            $base  = (float) $detalle->subtotal_con_igv;
            $qty   = (int) $detalle->cantidad;
            $monto = $regla->tipo_calculo === 'porcentaje'
                ? round($base * (float) $regla->valor / 100, 2)
                : round((float) $regla->valor * $qty, 2);

            ComisionDetalleVenta::create([
                'detalle_venta_id'  => $detalle->id,
                'user_id'           => $vendedorId,
                'regla_id'          => $regla->id,
                'tipo_calculo'      => $regla->tipo_calculo,
                'valor_configurado' => $regla->valor,
                'monto_comision'    => $monto,
                'estado'            => 'pendiente',
            ]);
        }
    }

    private function resolverRegla(int $vendedorId, int $productoId, ?int $categoriaId): ?ComisionRegla
    {
        // Producto-specific (highest priority)
        $regla = ComisionRegla::where('activo', true)
            ->where('tipo_aplicacion', 'producto')
            ->where('producto_id', $productoId)
            ->first();

        if ($regla) {
            return $regla;
        }

        // Category-specific
        if ($categoriaId) {
            $regla = ComisionRegla::where('activo', true)
                ->where('tipo_aplicacion', 'categoria')
                ->where('categoria_id', $categoriaId)
                ->first();
            if ($regla) {
                return $regla;
            }
        }

        // User-specific (lowest priority)
        return ComisionRegla::where('activo', true)
            ->where('tipo_aplicacion', 'usuario')
            ->where('user_id', $vendedorId)
            ->first();
    }
}
