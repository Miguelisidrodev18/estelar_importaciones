<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Imei extends Model
{
    use HasFactory;

    protected $fillable = [
        'codigo_imei',
        'serie',
        'color',
        'producto_id',
        'almacen_id',
        'estado',
    ];

    /**
     * Relación con Producto
     */
    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }

    /**
     * Relación con Almacén
     */
    public function almacen()
    {
        return $this->belongsTo(Almacen::class);
    }

    /**
     * Relación con MovimientoInventario
     */
    public function movimientos()
    {
        return $this->hasMany(MovimientoInventario::class);
    }

    /**
     * Scope para IMEIs disponibles
     */
    public function scopeDisponibles($query)
    {
        return $query->where('estado', 'disponible');
    }

    /**
     * Scope para IMEIs vendidos
     */
    public function scopeVendidos($query)
    {
        return $query->where('estado', 'vendido');
    }

    /**
     * Scope por almacén
     */
    public function scopePorAlmacen($query, $almacenId)
    {
        return $query->where('almacen_id', $almacenId);
    }

    /**
     * Accessor para nombre del producto
     */
    public function getNombreProductoAttribute()
    {
        return $this->producto->nombre ?? 'N/A';
    }

    /**
     * Accessor para nombre del almacén
     */
    public function getNombreAlmacenAttribute()
    {
        return $this->almacen->nombre ?? 'Sin asignar';
    }
}