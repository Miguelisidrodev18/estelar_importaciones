<?php

namespace App\Services;

use App\Models\BonusRegla;
use App\Models\BonusLiquidacion;
use App\Models\DetalleVenta;
use App\Models\Venta;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class BonusService
{
    /**
     * Punto de entrada principal: calcula bonos fijos y verifica metas al crear una venta.
     */
    public function calcularParaVenta(Venta $venta): void
    {
        $vendedorId = $venta->user_id;
        if (!$vendedorId) return;

        foreach ($venta->detalles as $detalle) {
            $productoId  = $detalle->producto_id;
            $categoriaId = $detalle->producto?->categoria_id;

            $reglasFijas = $this->resolverReglasFijas($productoId, $categoriaId);
            foreach ($reglasFijas as $regla) {
                $this->crearBonusFijo($regla, $detalle, $vendedorId);
            }

            $reglasMeta = $this->resolverReglasMeta($productoId, $categoriaId);
            foreach ($reglasMeta as $regla) {
                $this->verificarMeta($regla, $vendedorId, $productoId, $categoriaId, $venta->created_at ?? now());
            }
        }
    }

    // ── Bonos Fijos ──────────────────────────────────────────────────────────

    private function crearBonusFijo(BonusRegla $regla, DetalleVenta $detalle, int $vendedorId): void
    {
        $qty    = (int) $detalle->cantidad;
        $monto  = $regla->tipo_calculo === 'monto_fijo'
            ? round((float) $regla->valor * $qty, 2)
            : round((float) $detalle->subtotal_con_igv * (float) $regla->valor / 100, 2);

        if ($monto <= 0) return;

        BonusLiquidacion::create([
            'user_id'          => $vendedorId,
            'bonus_regla_id'   => $regla->id,
            'tipo_origen'      => 'fijo',
            'detalle_venta_id' => $detalle->id,
            'tipo_calculo'     => $regla->tipo_calculo,
            'valor_configurado'=> $regla->valor,
            'monto_bonus'      => $monto,
            'estado'           => 'pendiente',
        ]);
    }

    // ── Bonos de Meta ─────────────────────────────────────────────────────────

    private function verificarMeta(BonusRegla $regla, int $vendedorId, int $productoId, ?int $categoriaId, $fecha): void
    {
        [$inicio, $fin] = $this->periodoActual($regla->meta_periodo, Carbon::parse($fecha));

        // Si ya existe un bonus de meta para este período, no duplicar
        $existe = BonusLiquidacion::where('user_id', $vendedorId)
            ->where('bonus_regla_id', $regla->id)
            ->where('periodo_inicio', $inicio->toDateString())
            ->exists();

        if ($existe) return;

        // Contar unidades vendidas en el período por el vendedor
        $unidades = $this->contarUnidadesPeriodo($vendedorId, $productoId, $categoriaId, $regla->tipo_aplicacion, $inicio, $fin);

        if ($unidades < $regla->meta_unidades) return;

        // Calcular monto del bono de meta
        $monto = $regla->tipo_calculo === 'monto_fijo'
            ? round((float) $regla->valor, 2)
            : round($this->totalVentasPeriodo($vendedorId, $productoId, $categoriaId, $regla->tipo_aplicacion, $inicio, $fin) * (float) $regla->valor / 100, 2);

        if ($monto <= 0) return;

        BonusLiquidacion::create([
            'user_id'          => $vendedorId,
            'bonus_regla_id'   => $regla->id,
            'tipo_origen'      => 'meta',
            'periodo_inicio'   => $inicio->toDateString(),
            'periodo_fin'      => $fin->toDateString(),
            'unidades_periodo' => $unidades,
            'tipo_calculo'     => $regla->tipo_calculo,
            'valor_configurado'=> $regla->valor,
            'monto_bonus'      => $monto,
            'estado'           => 'pendiente',
        ]);
    }

    private function contarUnidadesPeriodo(int $vendedorId, int $productoId, ?int $categoriaId, string $tipoAplicacion, Carbon $inicio, Carbon $fin): int
    {
        $query = DetalleVenta::join('ventas', 'ventas.id', '=', 'detalle_ventas.venta_id')
            ->where('ventas.user_id', $vendedorId)
            ->whereBetween('ventas.created_at', [$inicio->startOfDay(), $fin->copy()->endOfDay()])
            ->whereIn('ventas.estado_pago', ['pagado', 'parcial', 'pendiente']);

        if ($tipoAplicacion === 'producto') {
            $query->where('detalle_ventas.producto_id', $productoId);
        } elseif ($tipoAplicacion === 'categoria' && $categoriaId) {
            $query->join('productos', 'productos.id', '=', 'detalle_ventas.producto_id')
                  ->where('productos.categoria_id', $categoriaId);
        }

        return (int) $query->sum('detalle_ventas.cantidad');
    }

    private function totalVentasPeriodo(int $vendedorId, int $productoId, ?int $categoriaId, string $tipoAplicacion, Carbon $inicio, Carbon $fin): float
    {
        $query = DetalleVenta::join('ventas', 'ventas.id', '=', 'detalle_ventas.venta_id')
            ->where('ventas.user_id', $vendedorId)
            ->whereBetween('ventas.created_at', [$inicio->startOfDay(), $fin->copy()->endOfDay()])
            ->whereIn('ventas.estado_pago', ['pagado', 'parcial', 'pendiente']);

        if ($tipoAplicacion === 'producto') {
            $query->where('detalle_ventas.producto_id', $productoId);
        } elseif ($tipoAplicacion === 'categoria' && $categoriaId) {
            $query->join('productos', 'productos.id', '=', 'detalle_ventas.producto_id')
                  ->where('productos.categoria_id', $categoriaId);
        }

        return (float) $query->sum('detalle_ventas.subtotal_con_igv');
    }

    // ── Resolvers ─────────────────────────────────────────────────────────────

    private function resolverReglasFijas(int $productoId, ?int $categoriaId): \Illuminate\Support\Collection
    {
        return BonusRegla::where('activo', true)
            ->where('tipo_bonus', 'fijo')
            ->where(function ($q) use ($productoId, $categoriaId) {
                $q->where(fn($q2) => $q2->where('tipo_aplicacion', 'producto')->where('producto_id', $productoId));
                if ($categoriaId) {
                    $q->orWhere(fn($q2) => $q2->where('tipo_aplicacion', 'categoria')->where('categoria_id', $categoriaId));
                }
            })
            ->get();
    }

    private function resolverReglasMeta(int $productoId, ?int $categoriaId): \Illuminate\Support\Collection
    {
        return BonusRegla::where('activo', true)
            ->where('tipo_bonus', 'meta')
            ->where(function ($q) use ($productoId, $categoriaId) {
                $q->where(fn($q2) => $q2->where('tipo_aplicacion', 'producto')->where('producto_id', $productoId));
                if ($categoriaId) {
                    $q->orWhere(fn($q2) => $q2->where('tipo_aplicacion', 'categoria')->where('categoria_id', $categoriaId));
                }
            })
            ->get();
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    public function periodoActual(string $periodo, ?Carbon $fecha = null): array
    {
        $fecha = $fecha ?? now();

        return match($periodo) {
            'semanal'    => [$fecha->copy()->startOfWeek(), $fecha->copy()->endOfWeek()],
            'quincenal'  => $fecha->day <= 15
                ? [$fecha->copy()->startOfMonth(), $fecha->copy()->startOfMonth()->addDays(14)]
                : [$fecha->copy()->startOfMonth()->addDays(15), $fecha->copy()->endOfMonth()],
            default      => [$fecha->copy()->startOfMonth(), $fecha->copy()->endOfMonth()], // mensual
        };
    }

    /**
     * Resumen de bonos pendientes y pagados por vendedor.
     */
    public function resumenPorVendedor(): \Illuminate\Support\Collection
    {
        return BonusLiquidacion::with('vendedor')
            ->selectRaw('user_id, estado, SUM(monto_bonus) as total')
            ->groupBy('user_id', 'estado')
            ->get()
            ->groupBy('user_id')
            ->map(function ($rows) {
                $pendiente = $rows->firstWhere('estado', 'pendiente');
                $pagado    = $rows->firstWhere('estado', 'pagado');
                return [
                    'vendedor'  => $rows->first()->vendedor,
                    'pendiente' => (float) ($pendiente->total ?? 0),
                    'pagado'    => (float) ($pagado->total   ?? 0),
                ];
            });
    }
}
