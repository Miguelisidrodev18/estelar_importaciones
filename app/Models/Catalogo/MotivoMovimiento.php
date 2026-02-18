<?php
// app/Models/Catalogo/MotivoMovimiento.php

namespace App\Models\Catalogo;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MotivoMovimiento extends Model
{
    use HasFactory;

    protected $table = 'motivos_movimiento';

    protected $fillable = [
        'nombre',
        'codigo',
        'tipo',
        'descripcion',
        'requiere_aprobacion',
        'afecta_stock',
        'estado'
    ];

    protected $casts = [
        'requiere_aprobacion' => 'boolean',
        'afecta_stock' => 'boolean'
    ];

    public function scopeActivos($query)
    {
        return $query->where('estado', 'activo');
    }

    public function scopePorTipo($query, $tipo)
    {
        return $query->where('tipo', $tipo);
    }
}