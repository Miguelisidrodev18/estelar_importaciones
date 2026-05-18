<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ComisionRegla extends Model
{
    protected $table = 'comision_reglas';

    protected $fillable = [
        'nombre', 'tipo_aplicacion', 'user_id', 'categoria_id', 'producto_id',
        'tipo_calculo', 'valor', 'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'valor'  => 'decimal:4',
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function categoria()
    {
        return $this->belongsTo(Categoria::class);
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }

    public function getTipoAplicacionLabelAttribute(): string
    {
        return match($this->tipo_aplicacion) {
            'usuario'   => 'Usuario',
            'categoria' => 'Categoría',
            'producto'  => 'Producto',
            default     => ucfirst($this->tipo_aplicacion),
        };
    }

    public function getTipoCalculoLabelAttribute(): string
    {
        return $this->tipo_calculo === 'porcentaje' ? 'Porcentaje (%)' : 'Monto Fijo (S/)';
    }
}
