<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Catalogo\Modelo;
use App\Models\Catalogo\Color;

class DetalleCompra extends Model
{
    use HasFactory; 
    protected $table = 'detalle_compras';


    protected $fillable = [
        'compra_id',
        'producto_id',
        'modelo_id',      // NUEVO
        'color_id',       // NUEVO
        'cantidad',
        'precio_unitario',
        'descuento',
        'subtotal',
        'codigo_barras',  // NUEVO
    ];

    protected $casts = [
        'cantidad' => 'integer',
        'precio_unitario' => 'decimal:2',
        'descuento' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    public function compra()
    {
        return $this->belongsTo(Compra::class);
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }

    public function modelo()
    {
        return $this->belongsTo(Modelo::class);
    }

    public function color()
    {
        return $this->belongsTo(Color::class);
    }
    public function imeis()
    {
        return $this->hasMany(Imei::class, 'detalle_compra_id');
    }
}
