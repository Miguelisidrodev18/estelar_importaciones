<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sucursal extends Model
{
    protected $table = 'sucursales';

    protected $fillable = [
        'codigo', 'nombre', 'direccion',
        'ubigeo', 'departamento', 'provincia', 'distrito',
        'telefono', 'email', 'almacen_id', 'es_principal', 'estado',
    ];

    protected $casts = [
        'es_principal' => 'boolean',
    ];

    // ── Relaciones ────────────────────────────────────────────

    /**
     * Almacén principal de la sucursal (campo almacen_id en sucursales)
     */
    public function almacen()
    {
        return $this->belongsTo(Almacen::class);
    }

    /**
     * Todos los almacenes que pertenecen a esta sucursal
     */
    public function almacenes()
    {
        return $this->hasMany(Almacen::class);
    }

    /**
     * Cajas abiertas en esta sucursal
     */
    public function cajas()
    {
        return $this->hasMany(\App\Models\Caja::class);
    }

    public function series()
    {
        return $this->hasMany(SerieComprobante::class);
    }

    public function pagos()
    {
        return $this->hasMany(ConfiguracionPagosSucursal::class);
    }

    public function ventas()
    {
        return $this->hasMany(Venta::class);
    }

    // ── Helpers ───────────────────────────────────────────────

    /**
     * Generar el próximo código de sucursal: S001, S002 …
     */
    public static function generarCodigo(): string
    {
        $ultimo = self::orderByDesc('id')->value('codigo');
        if (!$ultimo) {
            return 'S001';
        }
        $numero = (int) substr($ultimo, 1) + 1;
        return 'S' . str_pad($numero, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Series activas agrupadas por tipo.
     */
    public function seriesPorTipo(): array
    {
        return $this->series->where('activo', true)->groupBy('tipo_comprobante')->toArray();
    }
}
