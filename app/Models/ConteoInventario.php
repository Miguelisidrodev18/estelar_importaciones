<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConteoInventario extends Model
{
    protected $table = 'conteos_inventario';

    protected $fillable = [
        'nombre', 'almacen_id', 'user_id', 'estado', 'observaciones',
    ];

    protected $casts = [
        'estado' => 'string',
    ];

    public function almacen()
    {
        return $this->belongsTo(Almacen::class);
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function detalles()
    {
        return $this->hasMany(ConteoDetalle::class, 'conteo_id');
    }

    public function getContadosAttribute(): int
    {
        return $this->detalles()->whereNotNull('stock_fisico')->count();
    }

    public function getTotalLineasAttribute(): int
    {
        return $this->detalles()->count();
    }
}
