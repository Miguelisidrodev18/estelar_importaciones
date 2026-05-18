<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ComisionDetalleVenta extends Model
{
    protected $table = 'comision_detalle_venta';

    protected $fillable = [
        'detalle_venta_id', 'user_id', 'regla_id', 'tipo_calculo',
        'valor_configurado', 'monto_comision', 'estado', 'fecha_pago', 'pagado_por_user_id',
    ];

    protected $casts = [
        'monto_comision'    => 'decimal:2',
        'valor_configurado' => 'decimal:4',
        'fecha_pago'        => 'date',
    ];

    public function detalleVenta()
    {
        return $this->belongsTo(DetalleVenta::class);
    }

    public function vendedor()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function regla()
    {
        return $this->belongsTo(ComisionRegla::class, 'regla_id');
    }

    public function pagadoPor()
    {
        return $this->belongsTo(User::class, 'pagado_por_user_id');
    }
}
