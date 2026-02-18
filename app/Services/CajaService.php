<?php

namespace App\Services;

use App\Models\Caja;
use App\Models\MovimientoCaja;
use Illuminate\Support\Facades\DB;

class CajaService
{
    public function abrirCaja(int $userId, int $almacenId, float $montoInicial): Caja
    {
        $cajaAbierta = Caja::where('user_id', $userId)
            ->where('estado', 'abierta')
            ->first();

        if ($cajaAbierta) {
            throw new \Exception('Ya tienes una caja abierta. Ciérrala antes de abrir una nueva.');
        }

        return Caja::create([
            'user_id' => $userId,
            'almacen_id' => $almacenId,
            'fecha' => now()->toDateString(),
            'monto_inicial' => $montoInicial,
            'monto_final' => $montoInicial,
            'estado' => 'abierta',
        ]);
    }

    public function registrarMovimiento(int $cajaId, string $tipo, float $monto, string $concepto, ?int $ventaId = null, ?int $compraId = null): MovimientoCaja
    {
        return DB::transaction(function () use ($cajaId, $tipo, $monto, $concepto, $ventaId, $compraId) {

            $caja = Caja::findOrFail($cajaId);

            if ($caja->estado !== 'abierta') {
                throw new \Exception('La caja está cerrada');
            }

            $movimiento = MovimientoCaja::create([
                'caja_id' => $cajaId,
                'venta_id' => $ventaId,
                'compra_id' => $compraId,
                'tipo' => $tipo,
                'monto' => $monto,
                'concepto' => $concepto,
                'observaciones' => $observaciones,
            ]);

            if ($tipo === 'ingreso') {
                $caja->increment('monto_final', $monto);
            } else {
                $caja->decrement('monto_final', $monto);
            }

            return $movimiento;
        });
    }

    public function cerrarCaja(int $cajaId, float $montoFinalReal): Caja
    {
        return DB::transaction(function () use ($cajaId, $montoFinalReal) {

            $caja = Caja::findOrFail($cajaId);

            if ($caja->estado !== 'abierta') {
                throw new \Exception('Esta caja ya está cerrada');
            }

            $diferencia = $montoFinalReal - $caja->monto_final;

            $caja->update([
                'estado' => 'cerrada',
                'monto_final' => $montoFinalReal,
            ]);

            if (abs($diferencia) > 0.01) {
                MovimientoCaja::create([
                    'caja_id' => $cajaId,
                    'tipo' => $diferencia > 0 ? 'ingreso' : 'egreso',
                    'monto' => abs($diferencia),
                    'concepto' => $diferencia > 0 ? 'Sobrante en cierre de caja' : 'Faltante en cierre de caja',
                ]);
            }

            return $caja->fresh('movimientos');
        });
    }
}
