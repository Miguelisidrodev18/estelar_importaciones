<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Venta extends Model
{
    use HasFactory;

    protected $fillable = [
        'codigo',
        'user_id',
        'cliente_id',
        'almacen_id',
        'fecha',
        'subtotal',
        'igv',
        'total',
        'metodo_pago',
        'estado_pago',
        'usuario_confirma_id',
        'fecha_confirmacion',
        'observaciones',
        'tipo_venta', //'interna para tienda, externa para vendedores',
        'tienda_destino_id', //para ventas externas, a que tienda iban a pagar con esta venta
    ];

    protected $casts = [
        'fecha' => 'date',
        'fecha_confirmacion' => 'datetime',
        'subtotal' => 'decimal:2',
        'igv' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function vendedor()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function confirmador()
    {
        return $this->belongsTo(User::class, 'usuario_confirma_id');
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function almacen()
    {
        return $this->belongsTo(Almacen::class);
    }

    public function detalles()
    {
        return $this->hasMany(DetalleVenta::class);
    }

    public function scopePendientes($query)
    {
        return $query->where('estado_pago', 'pendiente');
    }

    public function scopePagadas($query)
    {
        return $query->where('estado_pago', 'pagado');
    }

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($venta) {
            if (empty($venta->codigo)) {
                $venta->codigo = self::generarCodigo();
            }
        });
    }

    public static function generarCodigo()
    {
        $ultimo = self::latest('id')->first();
        $numero = $ultimo ? $ultimo->id + 1 : 1;
        return 'VEN-' . str_pad($numero, 5, '0', STR_PAD_LEFT);
    }
}
