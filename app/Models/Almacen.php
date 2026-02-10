<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Almacen extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'almacenes';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nombre',
        'codigo',
        'direccion',
        'telefono',
        'encargado_id',
        'tipo',
        'estado',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'tipo' => 'string',
        'estado' => 'string',
    ];

    /**
     * Relación: Un almacén tiene un encargado (usuario)
     */
    public function encargado()
    {
        return $this->belongsTo(User::class, 'encargado_id');
    }

    /**
     * Relación: Un almacén tiene muchos movimientos de inventario
     */
    public function movimientos()
    {
        return $this->hasMany(MovimientoInventario::class);
    }

    /**
     * Relación: Movimientos donde este almacén es el destino
     */
    public function movimientosDestino()
    {
        return $this->hasMany(MovimientoInventario::class, 'almacen_destino_id');
    }

    /**
     * Scope para almacenes activos
     */
    public function scopeActivos($query)
    {
        return $query->where('estado', 'activo');
    }

    /**
     * Scope para almacenes inactivos
     */
    public function scopeInactivos($query)
    {
        return $query->where('estado', 'inactivo');
    }

    /**
     * Scope para almacén principal
     */
    public function scopePrincipal($query)
    {
        return $query->where('tipo', 'principal');
    }

    /**
     * Scope para sucursales
     */
    public function scopeSucursales($query)
    {
        return $query->where('tipo', 'sucursal');
    }

    /**
     * Obtener stock total del almacén
     */
    public function getStockTotalAttribute()
    {
        return $this->movimientos()
            ->selectRaw('SUM(CASE WHEN tipo_movimiento IN ("ingreso", "transferencia") THEN cantidad ELSE -cantidad END) as total')
            ->value('total') ?? 0;
    }

    /**
     * Obtener nombre completo del encargado
     */
    public function getNombreEncargadoAttribute()
    {
        return $this->encargado ? $this->encargado->name : 'Sin asignar';
    }

    /**
     * Verificar si es almacén principal
     */
    public function esPrincipal()
    {
        return $this->tipo === 'principal';
    }

    /**
     * Generar código automático para nuevo almacén
     */
    public static function generarCodigo()
    {
        $ultimoAlmacen = self::latest('id')->first();
        $numero = $ultimoAlmacen ? $ultimoAlmacen->id + 1 : 1;
        return 'ALM-' . str_pad($numero, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Boot method para eventos del modelo
     */
    protected static function boot()
    {
        parent::boot();

        // Antes de eliminar, verificar que no tenga movimientos
        static::deleting(function ($almacen) {
            if ($almacen->movimientos()->count() > 0) {
                throw new \Exception('No se puede eliminar un almacén que tiene movimientos registrados.');
            }
        });
    }
}