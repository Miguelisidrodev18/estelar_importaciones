<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GuiaRemisionDetalle extends Model
{
    protected $table = 'guia_remision_detalles';

    protected $fillable = [
        'guia_remision_id', 'producto_id', 'variante_id', 'cantidad', 'descripcion',
    ];

    public function guia()
    {
        return $this->belongsTo(GuiaRemision::class, 'guia_remision_id');
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }

    public function variante()
    {
        return $this->belongsTo(ProductoVariante::class, 'variante_id');
    }
}
