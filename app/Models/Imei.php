<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Catalogo\Color;
use Illuminate\Support\Facades\Storage;

class Imei extends Model
{
    use HasFactory;
    
    protected $table = 'imeis';

    protected $fillable = [
        'codigo_imei',
        'serie',
        'producto_id',
        'modelo_id',
        'color_id',
        'almacen_id',
        'compra_id',
        'detalle_compra_id',
        'venta_id',
        'estado_imei',        // renombrado desde 'estado'
        'fecha_ingreso',
        'fecha_venta',
        'fecha_garantia',      // 🔴 NUEVO: Fecha de fin de garantía
        'observaciones',
        'qr_path',             // 🔴 NUEVO: Ruta del código QR
        'usuario_registro_id', // 🔴 NUEVO: Usuario que registró
    ];

    protected $casts = [
        'fecha_ingreso'   => 'date',
        'fecha_venta'     => 'date',
        'fecha_garantia'  => 'date', // 🔴 NUEVO
        'created_at'      => 'datetime',
        'updated_at'      => 'datetime',
    ];

    // ─── Constantes para estados ──────────────────────────────────────────
    const ESTADO_EN_STOCK    = 'en_stock';
    const ESTADO_RESERVADO   = 'reservado';
    const ESTADO_VENDIDO     = 'vendido';
    const ESTADO_GARANTIA    = 'garantia';
    const ESTADO_DEVUELTO    = 'devuelto';
    const ESTADO_REEMPLAZADO = 'reemplazado';

    const ESTADOS = [
        self::ESTADO_EN_STOCK    => 'En Stock',
        self::ESTADO_RESERVADO   => 'Reservado',
        self::ESTADO_VENDIDO     => 'Vendido',
        self::ESTADO_GARANTIA    => 'En Garantía',
        self::ESTADO_DEVUELTO    => 'Devuelto',
        self::ESTADO_REEMPLAZADO => 'Reemplazado',
    ];

    // ─── Relaciones ──────────────────────────────────────────────────────────

    public function modelo()
    {
        return $this->belongsTo(\App\Models\Catalogo\Modelo::class);
    }

    public function color()
    {
        return $this->belongsTo(\App\Models\Catalogo\Color::class);
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }

    public function almacen()
    {
        return $this->belongsTo(Almacen::class);
    }

    public function compra()
    {
        return $this->belongsTo(Compra::class);
    }

    public function detalleCompra()
    {
        return $this->belongsTo(DetalleCompra::class);
    }

    public function venta()
    {
        return $this->belongsTo(Venta::class);
    }

    public function usuarioRegistro()
    {
        return $this->belongsTo(User::class, 'usuario_registro_id');
    }

    public function movimientos()
    {
        return $this->hasMany(MovimientoInventario::class, 'imei_id');
    }

    // ─── Accessors ─────────────────────────────────────────────────────────

    /**
     * Obtener URL pública del QR
     */
    public function getQrUrlAttribute(): ?string
    {
        return $this->qr_path ? Storage::url($this->qr_path) : null;
    }

    /**
     * Obtener nombre del estado en español
     */
    public function getEstadoNombreAttribute(): string
    {
        return self::ESTADOS[$this->estado_imei] ?? $this->estado_imei;
    }

    /**
     * Obtener clase CSS para el estado
     */
    public function getEstadoColorAttribute(): string
    {
        return match($this->estado_imei) {
            self::ESTADO_EN_STOCK    => 'green',
            self::ESTADO_RESERVADO   => 'yellow',
            self::ESTADO_VENDIDO     => 'red',
            self::ESTADO_GARANTIA    => 'blue',
            self::ESTADO_DEVUELTO    => 'orange',
            self::ESTADO_REEMPLAZADO => 'purple',
            default                   => 'gray',
        };
    }

    /**
     * Obtener badge HTML del estado
     */
    public function getEstadoBadgeAttribute(): string
    {
        $color = $this->estado_color;
        $nombre = $this->estado_nombre;
        
        return "<span class='px-3 py-1 text-xs font-medium rounded-full bg-{$color}-100 text-{$color}-800'>{$nombre}</span>";
    }

    /**
     * Verificar si está en stock
     */
    public function getEstaEnStockAttribute(): bool
    {
        return $this->estado_imei === self::ESTADO_EN_STOCK;
    }

    /**
     * Verificar si está vendido
     */
    public function getEstaVendidoAttribute(): bool
    {
        return $this->estado_imei === self::ESTADO_VENDIDO;
    }

    /**
     * Días restantes de garantía
     */
    public function getDiasGarantiaRestantesAttribute(): ?int
    {
        if (!$this->fecha_garantia) {
            return null;
        }
        
        return now()->diffInDays($this->fecha_garantia, false);
    }

    /**
     * ¿Está en garantía?
     */
    public function getEnGarantiaAttribute(): bool
    {
        return $this->estado_imei === self::ESTADO_GARANTIA || 
               ($this->fecha_garantia && now()->lessThan($this->fecha_garantia));
    }

    // ─── Scopes ─────────────────────────────────────────────────────────

    public function scopeDisponibles($query)
    {
        return $query->where('estado_imei', self::ESTADO_EN_STOCK);
    }

    public function scopeReservados($query)
    {
        return $query->where('estado_imei', self::ESTADO_RESERVADO);
    }

    public function scopeVendidos($query)
    {
        return $query->where('estado_imei', self::ESTADO_VENDIDO);
    }

    public function scopeEnGarantia($query)
    {
        return $query->where('estado_imei', self::ESTADO_GARANTIA);
    }

    public function scopeDevueltos($query)
    {
        return $query->where('estado_imei', self::ESTADO_DEVUELTO);
    }

    public function scopeReemplazados($query)
    {
        return $query->where('estado_imei', self::ESTADO_REEMPLAZADO);
    }

    public function scopePorProducto($query, $productoId)
    {
        return $query->where('producto_id', $productoId);
    }

    public function scopePorAlmacen($query, $almacenId)
    {
        return $query->where('almacen_id', $almacenId);
    }

    public function scopeConGarantiaVigente($query)
    {
        return $query->where(function($q) {
            $q->where('estado_imei', self::ESTADO_GARANTIA)
              ->orWhere('fecha_garantia', '>=', now());
        });
    }

    public function scopeConGarantiaVencida($query)
    {
        return $query->where('fecha_garantia', '<', now())
                     ->where('estado_imei', '!=', self::ESTADO_VENDIDO);
    }

    // ─── Métodos de acción ────────────────────────────────────────────────

    public function vender(?int $ventaId = null)
    {
        $this->update([
            'estado_imei' => self::ESTADO_VENDIDO,
            'venta_id' => $ventaId ?? $this->venta_id,
            'fecha_venta' => now(),
        ]);
    }

    public function enviarGarantia(int $diasGarantia = 30)
    {
        $this->update([
            'estado_imei' => self::ESTADO_GARANTIA,
            'fecha_garantia' => now()->addDays($diasGarantia),
        ]);
    }

    public function devolver()
    {
        $this->update([
            'estado_imei' => self::ESTADO_DEVUELTO,
            'fecha_garantia' => null,
        ]);
    }

    public function reemplazar()
    {
        $this->update([
            'estado_imei' => self::ESTADO_REEMPLAZADO,
        ]);
    }

    public function liberar()
    {
        $this->update([
            'estado_imei' => self::ESTADO_EN_STOCK,
            'venta_id' => null,
            'fecha_venta' => null,
        ]);
    }

    public function reservar()
    {
        $this->update([
            'estado_imei' => self::ESTADO_RESERVADO,
        ]);
    }

    /**
     * Cambiar estado con validaciones
     */
    public function cambiarEstado(string $nuevoEstado, ?array $data = [])
    {
        $transicionesPermitidas = [
            self::ESTADO_EN_STOCK => [self::ESTADO_RESERVADO, self::ESTADO_VENDIDO, self::ESTADO_GARANTIA],
            self::ESTADO_RESERVADO => [self::ESTADO_EN_STOCK, self::ESTADO_VENDIDO],
            self::ESTADO_VENDIDO => [self::ESTADO_GARANTIA, self::ESTADO_DEVUELTO],
            self::ESTADO_GARANTIA => [self::ESTADO_EN_STOCK, self::ESTADO_REEMPLAZADO, self::ESTADO_DEVUELTO],
            self::ESTADO_DEVUELTO => [self::ESTADO_EN_STOCK, self::ESTADO_GARANTIA],
            self::ESTADO_REEMPLAZADO => [self::ESTADO_EN_STOCK],
        ];

        if (!in_array($nuevoEstado, $transicionesPermitidas[$this->estado_imei] ?? [])) {
            throw new \Exception("No se puede cambiar de {$this->estado_imei} a {$nuevoEstado}");
        }

        $updateData = array_merge(['estado_imei' => $nuevoEstado], $data);
        
        if ($nuevoEstado === self::ESTADO_VENDIDO && !isset($updateData['fecha_venta'])) {
            $updateData['fecha_venta'] = now();
        }

        $this->update($updateData);
    }

    // ─── Boot events ──────────────────────────────────────────────────────

    protected static function booted()
    {
        static::creating(function ($imei) {
            // Si no tiene fecha de ingreso, poner la actual
            if (!$imei->fecha_ingreso) {
                $imei->fecha_ingreso = now();
            }
            
            // Validar formato IMEI (opcional)
            if (!preg_match('/^\d{15}$/', $imei->codigo_imei)) {
                throw new \Exception('El IMEI debe tener 15 dígitos numéricos');
            }
        });

        static::updating(function ($imei) {
            // Si cambia a vendido y no tiene fecha, ponerla
            if ($imei->isDirty('estado_imei') && 
                $imei->estado_imei === self::ESTADO_VENDIDO && 
                !$imei->fecha_venta) {
                $imei->fecha_venta = now();
            }
        });
    }
}