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
        'variante_id',
        'modelo_id',
        'color_id',
        'cantidad',
        'precio_unitario',
        'descuento',
        'subtotal',
        'codigo_barras',
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
    public function variante()
    {
        return $this->belongsTo(ProductoVariante::class, 'variante_id');
    }

    public function imeis()
    {
        return $this->hasMany(Imei::class, 'detalle_compra_id');
    }

    public function getImeisConLegacyAttribute()
    {
        // IMEIs con detalle_compra_id correcto (compras nuevas)
        $porDetalle = $this->imeis;

        // IMEIs sin detalle_compra_id (compras anteriores al fix), filtrados por variante
        $legacyQuery = \App\Models\Imei::where('compra_id', $this->compra_id)
            ->where('producto_id', $this->producto_id)
            ->whereNull('detalle_compra_id');

        if ($this->variante_id) {
            $legacyQuery->where('variante_id', $this->variante_id);
        } else {
            $legacyQuery->whereNull('variante_id');
        }

        return $porDetalle->merge($legacyQuery->get());
    }
}
