<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BonusLiquidacion extends Model
{
    protected $table = 'bonus_liquidaciones';

    protected $fillable = [
        'user_id', 'bonus_regla_id', 'tipo_origen',
        'detalle_venta_id',
        'periodo_inicio', 'periodo_fin', 'unidades_periodo',
        'tipo_calculo', 'valor_configurado', 'monto_bonus',
        'estado', 'fecha_pago', 'pagado_por_user_id',
    ];

    protected $casts = [
        'monto_bonus'       => 'decimal:2',
        'valor_configurado' => 'decimal:4',
        'periodo_inicio'    => 'date',
        'periodo_fin'       => 'date',
        'fecha_pago'        => 'date',
    ];

    public function vendedor()      { return $this->belongsTo(User::class, 'user_id'); }
    public function regla()         { return $this->belongsTo(BonusRegla::class, 'bonus_regla_id'); }
    public function detalleVenta()  { return $this->belongsTo(DetalleVenta::class, 'detalle_venta_id'); }
    public function pagadoPor()     { return $this->belongsTo(User::class, 'pagado_por_user_id'); }

    public function getEstadoLabelAttribute(): string
    {
        return $this->estado === 'pagado' ? 'Pagado' : 'Pendiente';
    }

    public function getEstadoCssAttribute(): string
    {
        return $this->estado === 'pagado'
            ? 'bg-green-100 text-green-700'
            : 'bg-amber-100 text-amber-700';
    }
}
