<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Catalogo\Color;

class Imei extends Model
{
    use HasFactory;
    protected $table = 'imeis';


    protected $fillable = [
        'codigo_imei',
        'serie',
        'producto_id',
        'modelo_id',        // NUEVO: ID del modelo específico
        'color_id',         // NUEVO: ID del color
        'almacen_id',
        'compra_id',
        'venta_id',
        'estado',
        'fecha_ingreso',
        'fecha_venta',
        'observaciones',
    ];
    protected $casts = [
        'fecha_ingreso' => 'date',
        'fecha_venta' => 'date',
    ];
    // Relaciones
     // NUEVA RELACIÓN: Modelo
    public function modelo()
    {
        return $this->belongsTo(\App\Models\Catalogo\Modelo::class);
    }

    // NUEVA RELACIÓN: Color
    public function color()
    {
        return $this->belongsTo(\App\Models\Catalogo\Color::class);
    }
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


    // Scopes
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