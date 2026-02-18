<?php
// app/Models/Catalogo/Marca.php

namespace App\Models\Catalogo;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Marca extends Model
{
    use HasFactory;

    protected $table = 'marcas';

    protected $fillable = [
        'nombre',
        'logo',
        'descripcion',
        'sitio_web',
        'estado'
    ];

    public function scopeActivos($query)
    {
        return $query->where('estado', 'activo');
    }

    public function modelos()
    {
        return $this->hasMany(Modelo::class);
    }
}