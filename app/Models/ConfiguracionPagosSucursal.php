<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConfiguracionPagosSucursal extends Model
{
    protected $table = 'configuracion_pagos_sucursal';

    protected $fillable = [
        'sucursal_id', 'tipo_pago', 'titular', 'numero',
        'banco', 'numero_cuenta', 'cci', 'qr_imagen_path', 'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class);
    }

    public function getQrUrlAttribute(): ?string
    {
        return $this->qr_imagen_path ? asset('storage/' . $this->qr_imagen_path) : null;
    }

    public function getNombreTipoAttribute(): string
    {
        return match($this->tipo_pago) {
            'yape'          => 'Yape',
            'plin'          => 'Plin',
            'transferencia' => 'Transferencia Bancaria',
            'pos'           => 'POS / Tarjeta',
            default         => ucfirst($this->tipo_pago),
        };
    }
}
