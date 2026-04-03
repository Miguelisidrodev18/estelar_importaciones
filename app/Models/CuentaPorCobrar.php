<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CuentaPorCobrar extends Model
{
    use HasFactory;

    protected $table = 'cuentas_por_cobrar';

    protected $fillable = [
        'venta_id',
        'cliente_id',
        'user_id',
        'monto_total',
        'monto_pagado',
        'numero_cuotas',
        'dias_entre_cuotas',
        'fecha_inicio',
        'fecha_vencimiento_final',
        'estado',
        'fecha_ultimo_pago',
        'observaciones',
    ];

    protected $casts = [
        'fecha_inicio'            => 'date',
        'fecha_vencimiento_final' => 'date',
        'fecha_ultimo_pago'       => 'date',
        'monto_total'             => 'decimal:2',
        'monto_pagado'            => 'decimal:2',
    ];

    // ─── Relaciones ──────────────────────────────────────────────────────────────

    public function venta()
    {
        return $this->belongsTo(Venta::class);
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function cuotas()
    {
        return $this->hasMany(CuotaCobro::class)->orderBy('numero_cuota');
    }

    public function pagos()
    {
        return $this->hasMany(PagoCredito::class)->orderBy('fecha_pago');
    }

    // ─── Atributos computados ────────────────────────────────────────────────────

    public function getSaldoPendienteAttribute(): float
    {
        return max(0, (float) $this->monto_total - (float) $this->monto_pagado);
    }

    public function getEstaVencidaAttribute(): bool
    {
        return $this->estado !== 'pagado'
            && $this->estado !== 'anulado'
            && now()->gt($this->fecha_vencimiento_final);
    }

    public function getPorcentajePagadoAttribute(): int
    {
        if ((float) $this->monto_total <= 0) return 0;
        return (int) min(100, round(((float) $this->monto_pagado / (float) $this->monto_total) * 100));
    }

    // ─── Scopes ──────────────────────────────────────────────────────────────────

    public function scopeVigentes($query)
    {
        return $query->where('estado', 'vigente');
    }

    public function scopeVencidas($query)
    {
        return $query->where('estado', 'vencido');
    }

    public function scopeActivas($query)
    {
        return $query->whereIn('estado', ['vigente', 'vencido']);
    }

    public function scopePorVencer($query, int $dias = 7)
    {
        return $query->where('estado', 'vigente')
            ->whereBetween('fecha_vencimiento_final', [now(), now()->addDays($dias)]);
    }
}
