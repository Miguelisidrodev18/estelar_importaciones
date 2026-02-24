<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CuentaPorPagar extends Model
{
    protected $table = 'cuentas_por_pagar';

    protected $fillable = [
        'compra_id',
        'proveedor_id',
        'numero_factura',
        'fecha_emision',
        'fecha_vencimiento',
        'monto_total',
        'monto_pagado',
        'moneda',
        'tipo_cambio',
        'estado',
        'dias_credito',
        'condiciones_pago',
        'fecha_ultimo_pago'
    ];

    protected $casts = [
        'fecha_emision' => 'date',
        'fecha_vencimiento' => 'date',
        'fecha_ultimo_pago' => 'date',
        'monto_total' => 'decimal:2',
        'monto_pagado' => 'decimal:2'
    ];

    public function compra()
    {
        return $this->belongsTo(Compra::class);
    }

    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class);
    }

    public function pagos()
    {
        return $this->hasMany(Pago::class, 'cuenta_por_pagar_id');
    }

    public function getSaldoPendienteAttribute()
    {
        return $this->monto_total - $this->monto_pagado;
    }

    public function getDiasVencimientoAttribute()
    {
        return now()->diffInDays($this->fecha_vencimiento, false);
    }

    public function getEstaVencidaAttribute()
    {
        return $this->estado != 'pagado' && now()->greaterThan($this->fecha_vencimiento);
    }

    public function getPorVencerAttribute()
    {
        $dias = $this->dias_vencimiento;
        return $dias > 0 && $dias <= 7;
    }
}