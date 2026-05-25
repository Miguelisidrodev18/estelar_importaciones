<?php

namespace App\Console\Commands;

use App\Models\Caja;
use App\Models\MovimientoCaja;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CerrarCajasAutomatico extends Command
{
    protected $signature   = 'caja:cerrar-automatico {--dry-run : Solo muestra qué cajas se cerrarían sin cerrarlas}';
    protected $description = 'Cierra automáticamente todas las cajas abiertas del día anterior (se ejecuta a medianoche hora Lima)';

    public function handle(): int
    {
        $hoy = now()->timezone('America/Lima')->toDateString();

        $cajas = Caja::where('estado', 'abierta')
            ->whereDate('fecha', '<', $hoy)
            ->with(['usuario', 'almacen', 'movimientos'])
            ->get();

        if ($cajas->isEmpty()) {
            $this->info('No hay cajas abiertas de días anteriores. Nada que cerrar.');
            return 0;
        }

        $this->info("Encontradas {$cajas->count()} caja(s) para cerrar automáticamente.");

        if ($this->option('dry-run')) {
            foreach ($cajas as $caja) {
                $this->line(" - Caja #{$caja->id} | {$caja->fecha} | {$caja->usuario?->name} | {$caja->almacen?->nombre}");
            }
            $this->warn('Modo dry-run: no se realizaron cambios.');
            return 0;
        }

        $cerradas = 0;
        foreach ($cajas as $caja) {
            try {
                DB::transaction(function () use ($caja) {
                    // Calcular saldo esperado de efectivo
                    $mov          = $caja->movimientos;
                    $ingresos     = $mov->where('tipo', 'ingreso');
                    $egresos      = $mov->where('tipo', 'egreso');
                    $efectivoIn   = $ingresos->whereNotNull('venta_id')->where('metodo_pago', 'efectivo')->sum('monto');
                    $manualIn     = $ingresos->whereNull('venta_id')->whereNotIn('concepto', ['Sobrante en cierre de caja'])->sum('monto');
                    $egresosTotal = $egresos->whereNotIn('concepto', ['Faltante en cierre de caja'])->sum('monto');
                    $saldoEsperado = (float) $caja->monto_inicial + $efectivoIn + $manualIn - $egresosTotal;

                    $caja->update([
                        'estado'               => 'cerrada',
                        'fecha_cierre'         => now(),
                        'monto_real_cierre'    => $saldoEsperado,
                        'diferencia_cierre'    => 0,
                        'observaciones_cierre' => 'Cierre automático al fin del día (sistema)',
                    ]);
                });

                $this->line("  ✓ Cerrada caja #{$caja->id} | {$caja->fecha} | {$caja->usuario?->name}");
                $cerradas++;
            } catch (\Throwable $e) {
                $this->error("  ✗ Error cerrando caja #{$caja->id}: {$e->getMessage()}");
            }
        }

        $this->info("Cierre automático completado: {$cerradas}/{$cajas->count()} caja(s) cerrada(s).");
        return 0;
    }
}
