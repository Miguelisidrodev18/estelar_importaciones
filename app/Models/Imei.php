<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Imei extends Model
{
    use HasFactory;

    protected $fillable = [
        'codigo_imei',
        'producto_id',
        'almacen_id',
        'compra_id',
        'serie',
        'color',
        'estado',
    ];

    // Relaciones
    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }

    public function almacen()
    {
        return $this->belongsTo(Almacen::class);
    }

    public function compra()
    {
        return $this->belongsTo(Compra::class);
    }

    // ✅ SCOPES QUE FALTAN
    public function scopeDisponibles($query)
    {
        return $query->where('estado', 'disponible');
    }

    public function scopeVendidos($query)
    {
        return $query->where('estado', 'vendido');
    }

    public function scopeReservados($query)
    {
        return $query->where('estado', 'reservado');
    }

    public function scopeDañados($query)
    {
        return $query->where('estado', 'dañado');
    }

    public function scopeEnAlmacen($query, $almacenId)
    {
        return $query->where('almacen_id', $almacenId);
    }

    public function scopeDeProducto($query, $productoId)
    {
        return $query->where('producto_id', $productoId);
    }

    // Métodos de acción
    public function reservar()
    {
        $this->update(['estado' => 'reservado']);
    }

    public function vender()
    {
        $this->update(['estado' => 'vendido']);
    }

    public function transferir($almacenDestinoId)
    {
        $this->update(['almacen_id' => $almacenDestinoId]);
    }

    public function marcarComoDefectuoso()
    {
        $this->update(['estado' => 'dañado']);
    }

    public function liberar()
    {
        $this->update(['estado' => 'disponible']);
    }
}