<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductoProveedor extends Model
{
    protected $table = 'productos_proveedor';

    protected $fillable = [
        'producto_id', 'proveedor_id', 'codigo_proveedor',
        'ultimo_precio_compra', 'ultima_fecha_compra',
        'plazo_entrega_dias', 'es_preferente', 'observaciones'
    ];

    protected $casts = [
        'es_preferente' => 'boolean',
        'ultima_fecha_compra' => 'date'
    ];

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }

    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class);
    }
}