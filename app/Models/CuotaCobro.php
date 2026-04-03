<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CuotaCobro extends Model
{
    use HasFactory;

    protected $table = 'cuotas_cobro';

    protected $fillable = [
        'cuenta_por_cobrar_id',
        'numero_cuota',
        'total_cuotas',
        'monto',
        'fecha_vencimiento',
        'estado',
        'fecha_pago_real',
    ];

    protected $casts = [
        'fecha_vencimiento' => 'date',
        'fecha_pago_real'   => 'date',
        'monto'             => 'decimal:2',
    ];

    // ─── Relaciones ──────────────────────────────────────────────────────────────

    public function cuentaPorCobrar()
    {
        return $this->belongsTo(CuentaPorCobrar::class);
    }

    public function pagos()
    {
        return $this->hasMany(PagoCredito::class, 'cuota_cobro_id');
    }

    // ─── Atributos computados ────────────────────────────────────────────────────

    public function getEstaVencidaAttribute(): bool
    {
        return $this->estado === 'pendiente' && now()->gt($this->fecha_vencimiento);
    }

    // ─── Scopes ──────────────────────────────────────────────────────────────────

    public function scopePendientes($query)
    {
        return $query->where('estado', 'pendiente');
    }

    public function scopeVencidas($query)
    {
        return $query->where('estado', 'pendiente')
            ->where('fecha_vencimiento', '<', now());
    }
}
