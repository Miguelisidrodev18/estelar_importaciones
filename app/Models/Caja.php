<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Caja extends Model
{
    use HasFactory;

    protected $table = 'caja';

    protected $fillable = [
        'user_id',
        'almacen_id',
        'fecha',
        'monto_inicial',
        'monto_final',
        'estado',
    ];

    protected $casts = [
        'fecha' => 'date',
        'monto_inicial' => 'decimal:2',
        'monto_final' => 'decimal:2',
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function almacen()
    {
        return $this->belongsTo(Almacen::class);
    }

    public function movimientos()
    {
        return $this->hasMany(MovimientoCaja::class);
    }

    public function scopeAbiertas($query)
    {
        return $query->where('estado', 'abierta');
    }

    public function getTotalIngresosAttribute()
    {
        return $this->movimientos()->where('tipo', 'ingreso')->sum('monto');
    }

    public function getTotalEgresosAttribute()
    {
        return $this->movimientos()->where('tipo', 'egreso')->sum('monto');
    }
}
