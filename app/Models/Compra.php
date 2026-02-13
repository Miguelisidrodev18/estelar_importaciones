<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Compra extends Model
{
    use HasFactory;

    protected $fillable = [
        'codigo',
        'proveedor_id',
        'user_id',
        'almacen_id',
        'numero_factura',
        'fecha',
        'subtotal',
        'igv',
        'total',
        'estado',
        'observaciones',
    ];

    protected $casts = [
        'fecha' => 'date',
        'subtotal' => 'decimal:2',
        'igv' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class);
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function almacen()
    {
        return $this->belongsTo(Almacen::class);
    }

    public function detalles()
    {
        return $this->hasMany(DetalleCompra::class);
    }

    public function imeis()
    {
        return $this->hasMany(Imei::class);
    }

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($compra) {
            if (empty($compra->codigo)) {
                $compra->codigo = self::generarCodigo();
            }
        });
    }

    public static function generarCodigo()
    {
        $ultimo = self::latest('id')->first();
        $numero = $ultimo ? $ultimo->id + 1 : 1;
        return 'COM-' . str_pad($numero, 5, '0', STR_PAD_LEFT);
    }
}
