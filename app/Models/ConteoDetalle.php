<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConteoDetalle extends Model
{
    protected $table = 'conteo_detalles';

    protected $fillable = [
        'conteo_id', 'producto_id', 'variante_id',
        'stock_sistema', 'stock_fisico', 'contado_at', 'observaciones',
    ];

    protected $casts = [
        'stock_sistema' => 'integer',
        'stock_fisico'  => 'integer',
        'contado_at'    => 'datetime',
    ];

    public function conteo()
    {
        return $this->belongsTo(ConteoInventario::class, 'conteo_id');
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }

    public function variante()
    {
        return $this->belongsTo(ProductoVariante::class, 'variante_id');
    }

    public function getDiferenciaAttribute(): ?int
    {
        if ($this->stock_fisico === null) {
            return null;
        }
        return $this->stock_fisico - $this->stock_sistema;
    }

    public function getFaltanteAttribute(): int
    {
        $dif = $this->diferencia;
        return $dif !== null && $dif < 0 ? abs($dif) : 0;
    }

    public function getValorFaltanteAttribute(): float
    {
        return $this->faltante * (float) ($this->producto?->costo_promedio ?? 0);
    }
}
