<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockAlmacen extends Model
{
    use HasFactory;

    protected $table = 'stock_almacen';

    protected $fillable = [
        'producto_id',
        'almacen_id',
        'cantidad',
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
     * Scope para stock con cantidad mayor a 0
     */
    public function scopeConStock($query)
    {
        return $query->where('cantidad', '>', 0);
    }

    /**
     * Scope por almacén
     */
    public function scopePorAlmacen($query, $almacenId)
    {
        return $query->where('almacen_id', $almacenId);
    }

    /**
     * Scope por producto
     */
    public function scopePorProducto($query, $productoId)
    {
        return $query->where('producto_id', $productoId);
    }

    /**
     * Método estático para obtener o crear stock
     */
    public static function obtenerOCrear($productoId, $almacenId)
    {
        return self::firstOrCreate(
            [
                'producto_id' => $productoId,
                'almacen_id' => $almacenId,
            ],
            [
                'cantidad' => 0,
            ]
        );
    }

    /**
     * Método para incrementar stock
     */
    public function incrementar($cantidad)
    {
        $this->increment('cantidad', $cantidad);
        return $this;
    }

    /**
     * Método para decrementar stock
     */
    public function decrementar($cantidad)
    {
        if ($this->cantidad >= $cantidad) {
            $this->decrement('cantidad', $cantidad);
            return $this;
        }
        
        throw new \Exception('Stock insuficiente en almacén');
    }
}