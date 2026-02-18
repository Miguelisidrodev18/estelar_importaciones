<?php
// app/Models/Catalogo/Color.php

namespace App\Models\Catalogo;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Color extends Model
{
    use HasFactory;

    protected $table = 'colores';

    protected $fillable = [
        'nombre',
        'codigo_hex',
        'codigo_color',
        'descripcion',
        'estado'
    ];

    public function scopeActivos($query)
    {
        return $query->where('estado', 'activo');
    }

    public function getColorPreviewAttribute()
    {
        return $this->codigo_hex ?? '#cccccc';
    }
}