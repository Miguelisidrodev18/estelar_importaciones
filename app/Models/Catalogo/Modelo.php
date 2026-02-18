<?php
// app/Models/Catalogo/Modelo.php

namespace App\Models\Catalogo;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Modelo extends Model
{
    use HasFactory;

    protected $table = 'modelos';

    protected $fillable = [
        'nombre',
        'marca_id',
        'categoria_id',
        'imagen_referencia',
        'codigo_modelo',
        'especificaciones_tecnicas',
        'estado'
    ];

    public function scopeActivos($query)
    {
        return $query->where('estado', 'activo');
    }

    public function marca()
    {
        return $this->belongsTo(Marca::class);
    }

    public function categoria()
    {
    return $this->belongsTo(\App\Models\Categoria::class, 'categoria_id');    }
    public function scopePorMarca($query, $marcaId)
    {
        return $query->where('marca_id', $marcaId);
    }
    }