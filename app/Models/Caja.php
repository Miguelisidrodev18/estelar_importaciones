<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Caja extends Model
{
    use HasFactory;

    protected $table = 'caja';

    protected $fillable = [
        'user_id', 'almacen_id', 'sucursal_id',
        'fecha', 'fecha_apertura', 'fecha_cierre',
        'monto_inicial', 'monto_final', 'monto_real_cierre', 'diferencia_cierre',
        'estado',
        'observaciones_apertura', 'observaciones_cierre',
    ];

    protected $casts = [
        'fecha'             => 'date',
        'fecha_apertura'    => 'datetime',
        'fecha_cierre'      => 'datetime',
        'monto_inicial'     => 'decimal:2',
        'monto_final'       => 'decimal:2',
        'monto_real_cierre' => 'decimal:2',
        'diferencia_cierre' => 'decimal:2',
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function almacen()
    {
        return $this->belongsTo(Almacen::class);
    }

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class);
    }

    public function movimientos()
    {
        return $this->hasMany(MovimientoCaja::class);
    }

    public function scopeAbiertas($query)
    {
        return $query->where('estado', 'abierta');
    }

    public function scopeCerradas($query)
    {
        return $query->where('estado', 'cerrada');
    }

    public function getTotalIngresosAttribute()
    {
        return $this->movimientos()->where('tipo', 'ingreso')->sum('monto');
    }

    public function getTotalEgresosAttribute()
    {
        return $this->movimientos()->where('tipo', 'egreso')->sum('monto');
    }

    public function getTotalVentasAttribute()
    {
        return $this->movimientos()->where('tipo', 'ingreso')->whereNotNull('venta_id')->sum('monto');
    }
}
