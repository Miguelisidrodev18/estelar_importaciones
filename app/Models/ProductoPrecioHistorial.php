<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductoPrecioHistorial extends Model
{
    protected $table = 'producto_precios_historial';

    protected $fillable = [
        'producto_id',
        'tipo_cambio',
        'precio_anterior',
        'precio_nuevo',
        'moneda',
        'motivo',
        'usuario_id'
    ];

    protected $casts = [
        'precio_anterior' => 'decimal:2',
        'precio_nuevo' => 'decimal:2'
    ];

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}