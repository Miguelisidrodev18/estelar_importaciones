<?php
// app/Models/Catalogo/UnidadMedida.php

namespace App\Models\Catalogo;

use Illuminate\Database\Eloquent\Model;

class UnidadMedida extends Model
{
    protected $table = 'unidades_medida';

    protected $fillable = [
        'nombre',
        'abreviatura',
        'categoria', // Tipo de medida (peso, volumen, etc)
        'categoria_inventario_id', // Relación con categoría de inventario
        'descripcion',
        'permite_decimales',
        'estado'
    ];

    protected $casts = [
        'permite_decimales' => 'boolean'
    ];

    public function categoriaInventario()
    {
        return $this->belongsTo(\App\Models\Categoria::class, 'categoria_inventario_id');
    }

    public function scopeActivos($query)
    {
        return $query->where('estado', 'activo');
    }
}