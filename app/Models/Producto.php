<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'codigo',
        'nombre',
        'tipo_producto',
        'descripcion',
        'categoria_id',
        'marca',
        'modelo',
        'unidad_medida',
        'codigo_barras',
        'imagen',
        'precio_compra_actual',
        'precio_venta',
        'precio_mayorista',
        'stock_actual',
        'stock_minimo',
        'stock_maximo',
        'ubicacion',
        'estado',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'precio_compra_actual' => 'decimal:2',
        'precio_venta' => 'decimal:2',
        'precio_mayorista' => 'decimal:2',
        'stock_actual' => 'integer',
        'stock_minimo' => 'integer',
        'stock_maximo' => 'integer',
        'estado' => 'string',
    ];

    /**
     * Relación: Un producto pertenece a una categoría
     */
    public function categoria()
    {
        return $this->belongsTo(Categoria::class);
    }

    /**
     * Relación: Un producto tiene muchos movimientos de inventario
     */
    public function movimientos()
    {
        return $this->hasMany(MovimientoInventario::class);
    }

    /**
     * Scope para productos activos
     */
    public function scopeActivos($query)
    {
        return $query->where('estado', 'activo');
    }

    /**
     * Scope para productos con stock bajo
     */
    public function scopeStockBajo($query)
    {
        return $query->whereRaw('stock_actual <= stock_minimo');
    }

    /**
     * Scope para productos sin stock
     */
    public function scopeSinStock($query)
    {
        return $query->where('stock_actual', 0);
    }

    /**
     * Scope para productos por categoría
     */
    public function scopeDeCategoria($query, $categoriaId)
    {
        return $query->where('categoria_id', $categoriaId);
    }

    /**
     * Scope para búsqueda por código o nombre
     */
    public function scopeBuscar($query, $termino)
    {
        return $query->where(function($q) use ($termino) {
            $q->where('codigo', 'like', "%{$termino}%")
                ->orWhere('nombre', 'like', "%{$termino}%")
                ->orWhere('codigo_barras', 'like', "%{$termino}%");
        });
    }
    protected $appends = ['imagen_url'];

    /**
     * Accessor: URL completa de la imagen
     */
    public function getImagenUrlAttribute()
    {
        if ($this->imagen) {
            return asset('storage/' . $this->imagen);
        }

        return null;
    }

    /**
     * Accessor: Nombre de categoría
     */
    public function getNombreCategoriaAttribute()
    {
        return $this->categoria ? $this->categoria->nombre : 'Sin categoría';
    }

    /**
     * Accessor: Margen de ganancia
     */
    public function getMargenGananciaAttribute()
    {
        if ($this->precio_compra_actual == 0) {
            return 0;
        }
        
        $margen = (($this->precio_venta - $this->precio_compra_actual) / $this->precio_compra_actual) * 100;
        return round($margen, 2);
    }

    /**
     * Accessor: Estado del stock
     */
    public function getEstadoStockAttribute()
    {
        if ($this->stock_actual == 0) {
            return 'sin_stock';
        } elseif ($this->stock_actual <= $this->stock_minimo) {
            return 'bajo';
        } elseif ($this->stock_actual >= $this->stock_maximo) {
            return 'exceso';
        }
        return 'normal';
    }

    /**
     * Accessor: Color del estado de stock para UI
     */
    public function getColorEstadoStockAttribute()
    {
        return match($this->estado_stock) {
            'sin_stock' => 'red',
            'bajo' => 'yellow',
            'exceso' => 'blue',
            default => 'green',
        };
    }

    /**
     * Verificar si el producto está activo
     */
    public function estaActivo()
    {
        return $this->estado === 'activo';
    }

    /**
     * Verificar si tiene stock disponible
     */
    public function tieneStock($cantidad = 1)
    {
        return $this->stock_actual >= $cantidad;
    }

    /**
     * Verificar si está en stock bajo
     */
    public function stockBajo()
    {
        return $this->stock_actual <= $this->stock_minimo;
    }

    /**
     * Incrementar stock
     */
    public function incrementarStock($cantidad)
    {
        $this->increment('stock_actual', $cantidad);
    }

    /**
     * Decrementar stock
     */
    public function decrementarStock($cantidad)
    {
        if ($this->stock_actual < $cantidad) {
            throw new \Exception('Stock insuficiente para el producto: ' . $this->nombre);
        }
        $this->decrement('stock_actual', $cantidad);
    }

    /**
     * Calcular valor total del inventario de este producto
     */
    public function getValorInventarioAttribute()
    {
        return $this->stock_actual * $this->precio_compra_actual;
    }

    /**
     * Generar código automático para nuevo producto
     */
    public static function generarCodigo()
    {
        $ultimoProducto = self::latest('id')->first();
        $numero = $ultimoProducto ? $ultimoProducto->id + 1 : 1;
        return 'PROD-' . str_pad($numero, 5, '0', STR_PAD_LEFT);
    }

    /**
     * Obtener productos más vendidos (placeholder para cuando tengamos ventas)
     */
    public static function masVendidos($limite = 10)
    {
        // TODO: Implementar cuando tengamos el módulo de ventas
        return self::activos()->limit($limite)->get();
    }

    /**
     * Boot method para eventos del modelo
     */
    protected static function boot()
    {
        parent::boot();

        // Antes de eliminar, verificar que no tenga movimientos
        static::deleting(function ($producto) {
            if ($producto->movimientos()->count() > 0) {
                throw new \Exception('No se puede eliminar un producto que tiene movimientos registrados.');
            }
        });
    }
}