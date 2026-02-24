<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cuota extends Model
{
    protected $table = 'cuotas';

    protected $fillable = [
        'cuenta_por_pagar_id',
        'numero_cuota',
        'total_cuotas',
        'monto',
        'fecha_vencimiento',
        'estado',
        'pago_id',
    ];

    protected $casts = [
        'fecha_vencimiento' => 'date',
        'monto'             => 'decimal:2',
    ];

    public function cuentaPorPagar()
    {
        return $this->belongsTo(CuentaPorPagar::class, 'cuenta_por_pagar_id');
    }

    public function pago()
    {
        return $this->belongsTo(Pago::class);
    }

    public function getEstaVencidaAttribute(): bool
    {
        return $this->estado === 'pendiente' && now()->greaterThan($this->fecha_vencimiento);
    }
}
