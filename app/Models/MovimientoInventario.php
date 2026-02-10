<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MovimientoInventario extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'movimientos_inventario';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'producto_id',
        'almacen_id',
        'user_id',
        'tipo_movimiento',
        'cantidad',
        'stock_anterior',
        'stock_nuevo',
        'motivo',
        'observaciones',
        'documento_referencia',
        'almacen_destino_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'cantidad' => 'integer',
        'stock_anterior' => 'integer',
        'stock_nuevo' => 'integer',
        'tipo_movimiento' => 'string',
    ];

    /**
     * Relación: Un movimiento pertenece a un producto
     */
    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }

    /**
     * Relación: Un movimiento pertenece a un almacén
     */
    public function almacen()
    {
        return $this->belongsTo(Almacen::class);
    }

    /**
     * Relación: Un movimiento pertenece a un almacén destino (transferencias)
     */
    public function almacenDestino()
    {
        return $this->belongsTo(Almacen::class, 'almacen_destino_id');
    }

    /**
     * Relación: Un movimiento es registrado por un usuario
     */
    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Scope para movimientos de ingreso
     */
    public function scopeIngresos($query)
    {
        return $query->where('tipo_movimiento', 'ingreso');
    }

    /**
     * Scope para movimientos de salida
     */
    public function scopeSalidas($query)
    {
        return $query->where('tipo_movimiento', 'salida');
    }

    /**
     * Scope para movimientos de ajuste
     */
    public function scopeAjustes($query)
    {
        return $query->where('tipo_movimiento', 'ajuste');
    }

    /**
     * Scope para transferencias
     */
    public function scopeTransferencias($query)
    {
        return $query->where('tipo_movimiento', 'transferencia');
    }

    /**
     * Scope para movimientos de hoy
     */
    public function scopeHoy($query)
    {
        return $query->whereDate('created_at', today());
    }

    /**
     * Scope para movimientos por producto
     */
    public function scopeDeProducto($query, $productoId)
    {
        return $query->where('producto_id', $productoId);
    }

    /**
     * Scope para movimientos por almacén
     */
    public function scopeDeAlmacen($query, $almacenId)
    {
        return $query->where('almacen_id', $almacenId);
    }

    /**
     * Scope para movimientos por usuario
     */
    public function scopeDeUsuario($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope para movimientos entre fechas
     */
    public function scopeEntreFechas($query, $fechaInicio, $fechaFin)
    {
        return $query->whereBetween('created_at', [$fechaInicio, $fechaFin]);
    }

    /**
     * Accessor: Nombre del tipo de movimiento en español
     */
    public function getTipoMovimientoNombreAttribute()
    {
        return match($this->tipo_movimiento) {
            'ingreso' => 'Ingreso',
            'salida' => 'Salida',
            'ajuste' => 'Ajuste',
            'transferencia' => 'Transferencia',
            'devolucion' => 'Devolución',
            'merma' => 'Merma',
            default => 'Desconocido',
        };
    }

    /**
     * Accessor: Color del tipo de movimiento para UI
     */
    public function getColorTipoMovimientoAttribute()
    {
        return match($this->tipo_movimiento) {
            'ingreso' => 'green',
            'salida' => 'red',
            'ajuste' => 'blue',
            'transferencia' => 'purple',
            'devolucion' => 'orange',
            'merma' => 'gray',
            default => 'gray',
        };
    }

    /**
     * Accessor: Icono del tipo de movimiento
     */
    public function getIconoTipoMovimientoAttribute()
    {
        return match($this->tipo_movimiento) {
            'ingreso' => 'fa-arrow-down',
            'salida' => 'fa-arrow-up',
            'ajuste' => 'fa-sliders-h',
            'transferencia' => 'fa-exchange-alt',
            'devolucion' => 'fa-undo',
            'merma' => 'fa-trash',
            default => 'fa-question',
        };
    }

    /**
     * Accessor: Nombre del usuario que registró el movimiento
     */
    public function getNombreUsuarioAttribute()
    {
        return $this->usuario ? $this->usuario->name : 'Sistema';
    }

    /**
     * Accessor: Nombre del almacén
     */
    public function getNombreAlmacenAttribute()
    {
        return $this->almacen ? $this->almacen->nombre : 'N/A';
    }

    /**
     * Accessor: Diferencia de stock
     */
    public function getDiferenciaStockAttribute()
    {
        return $this->stock_nuevo - $this->stock_anterior;
    }

    /**
     * Verificar si es un ingreso
     */
    public function esIngreso()
    {
        return $this->tipo_movimiento === 'ingreso';
    }

    /**
     * Verificar si es una salida
     */
    public function esSalida()
    {
        return $this->tipo_movimiento === 'salida';
    }

    /**
     * Verificar si es una transferencia
     */
    public function esTransferencia()
    {
        return $this->tipo_movimiento === 'transferencia';
    }

    /**
     * Crear un movimiento de inventario y actualizar el stock del producto
     */
    public static function registrarMovimiento($datos)
    {
        return \DB::transaction(function () use ($datos) {
            $producto = Producto::findOrFail($datos['producto_id']);
            $stockAnterior = $producto->stock_actual;

            // Calcular nuevo stock según tipo de movimiento
            $cantidad = $datos['cantidad'];
            $stockNuevo = $stockAnterior;

            switch ($datos['tipo_movimiento']) {
                case 'ingreso':
                case 'devolucion':
                    $stockNuevo += $cantidad;
                    break;
                case 'salida':
                case 'merma':
                    $stockNuevo -= $cantidad;
                    break;
                case 'ajuste':
                    // El ajuste puede ser positivo o negativo
                    $stockNuevo = $datos['stock_nuevo'] ?? $stockAnterior;
                    break;
                case 'transferencia':
                    $stockNuevo -= $cantidad;
                    break;
            }

            // Verificar que el stock no quede negativo
            if ($stockNuevo < 0) {
                throw new \Exception('No hay suficiente stock para realizar este movimiento.');
            }

            // Determinar user_id (usar el proporcionado, el autenticado, o el primer admin)
            $userId = $datos['user_id'] ?? auth()->id();
            
            // Si no hay usuario autenticado (ej: Tinker), usar el primer usuario admin
            if (!$userId) {
                $userId = \App\Models\User::whereHas('role', function($q) {
                    $q->where('nombre', 'Administrador');
                })->first()->id ?? 1;
            }

            // Crear el movimiento
            $movimiento = self::create([
                'producto_id' => $datos['producto_id'],
                'almacen_id' => $datos['almacen_id'],
                'user_id' => $userId,
                'tipo_movimiento' => $datos['tipo_movimiento'],
                'cantidad' => $cantidad,
                'stock_anterior' => $stockAnterior,
                'stock_nuevo' => $stockNuevo,
                'motivo' => $datos['motivo'] ?? null,
                'observaciones' => $datos['observaciones'] ?? null,
                'documento_referencia' => $datos['documento_referencia'] ?? null,
                'almacen_destino_id' => $datos['almacen_destino_id'] ?? null,
            ]);

            // Actualizar stock del producto
            $producto->update(['stock_actual' => $stockNuevo]);

            return $movimiento;
        });
    }

    /**
     * Boot method para eventos del modelo
     */
    protected static function boot()
    {
        parent::boot();

        // Los movimientos NO se pueden eliminar, solo se pueden compensar con nuevos movimientos
        static::deleting(function ($movimiento) {
            throw new \Exception('Los movimientos de inventario no se pueden eliminar por trazabilidad. Realice un movimiento de ajuste.');
        });
    }
}