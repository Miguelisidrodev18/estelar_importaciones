<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PagoCredito extends Model
{
    use HasFactory;

    protected $table = 'pagos_credito';

    protected $fillable = [
        'cuenta_por_cobrar_id',
        'cuota_cobro_id',
        'usuario_id',
        'monto',
        'fecha_pago',
        'metodo_pago',
        'referencia',
        'observaciones',
    ];

    protected $casts = [
        'fecha_pago' => 'date',
        'monto'      => 'decimal:2',
    ];

    // ─── Relaciones ──────────────────────────────────────────────────────────────

    public function cuentaPorCobrar()
    {
        return $this->belongsTo(CuentaPorCobrar::class);
    }

    public function cuota()
    {
        return $this->belongsTo(CuotaCobro::class, 'cuota_cobro_id');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}
