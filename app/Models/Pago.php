<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pago extends Model
{
    protected $table = 'pagos';

    protected $fillable = [
        'cuenta_por_pagar_id',
        'monto',
        'fecha_pago',
        'metodo_pago',
        'referencia',
        'banco_origen',
        'cuenta_origen',
        'estado',
        'fecha_programacion',
        'usuario_id',
        'observaciones',
        'comprobante_path',
    ];

    protected $casts = [
        'fecha_pago' => 'date',
        'fecha_programacion' => 'date',
        'monto' => 'decimal:2',
    ];

    public function cuentaPorPagar()
    {
        return $this->belongsTo(CuentaPorPagar::class, 'cuenta_por_pagar_id');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}