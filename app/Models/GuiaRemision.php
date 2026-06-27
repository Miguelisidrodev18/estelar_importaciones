<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GuiaRemision extends Model
{
    protected $table = 'guias_remision';

    protected $fillable = [
        'almacen_id', 'tipo_destino', 'almacen_destino_id',
        'venta_id', 'proveedor_id', 'cliente_id',
        'numero_guia', 'guia_serie_id',
        'motivo_traslado', 'modalidad', 'fecha_traslado',
        'peso_total', 'bultos',
        'direccion_partida', 'ubigeo_partida',
        'direccion_llegada', 'ubigeo_llegada',
        'transportista_tipo_doc', 'transportista_doc', 'transportista_nombre',
        'conductor_dni', 'conductor_nombre', 'conductor_licencia', 'placa_vehiculo',
        'estado',
        'sunat_estado', 'sunat_api_id', 'sunat_ticket',
        'sunat_cdr_code', 'sunat_descripcion', 'sunat_enviado_at',
    ];

    protected $casts = [
        'fecha_traslado'   => 'date',
        'peso_total'       => 'decimal:2',
        'sunat_enviado_at' => 'datetime',
    ];

    // ── Relaciones ────────────────────────────────────────────────

    public function venta()      { return $this->belongsTo(Venta::class); }
    public function proveedor()  { return $this->belongsTo(Proveedor::class); }
    public function cliente()    { return $this->belongsTo(Cliente::class); }
    public function almacen()    { return $this->belongsTo(Almacen::class); }
    public function almacenDestino() { return $this->belongsTo(Almacen::class, 'almacen_destino_id'); }
    public function serieCombrobante() { return $this->belongsTo(SerieComprobante::class, 'guia_serie_id'); }

    public function detalles()
    {
        return $this->hasMany(GuiaRemisionDetalle::class, 'guia_remision_id');
    }

    public function movimientos()
    {
        return $this->hasMany(MovimientoInventario::class, 'numero_guia', 'numero_guia');
    }

    // ── Accessors ─────────────────────────────────────────────────

    public function getEstadoLabelAttribute(): string
    {
        return match($this->estado) {
            'pendiente'   => 'Pendiente',
            'en_transito' => 'En Tránsito',
            'entregada'   => 'Entregada',
            'anulada'     => 'Anulada',
            default       => ucfirst($this->estado ?? 'pendiente'),
        };
    }

    public function getEstadoCssAttribute(): string
    {
        return match($this->estado) {
            'pendiente'   => 'bg-amber-100 text-amber-700',
            'en_transito' => 'bg-blue-100 text-blue-700',
            'entregada'   => 'bg-green-100 text-green-700',
            'anulada'     => 'bg-red-100 text-red-700',
            default       => 'bg-gray-100 text-gray-500',
        };
    }

    public function getMotivoLabelAttribute(): string
    {
        return match($this->motivo_traslado) {
            'VENTA'                    => 'Venta',
            'COMPRA'                   => 'Compra',
            'TRASLADO_ENTRE_ALMACENES' => 'Traslado entre almacenes',
            'CONSIGNACION'             => 'Consignación',
            'IMPORTACION'              => 'Importación',
            'EXPORTACION'              => 'Exportación',
            'DEVOLUCION'               => 'Devolución',
            default                    => 'Otros',
        };
    }

    public function getModalidadLabelAttribute(): string
    {
        return $this->modalidad === 'privado' ? 'Transporte Privado' : 'Transporte Público';
    }

    public function getTipoDestinoLabelAttribute(): string
    {
        return match($this->tipo_destino) {
            'almacen'   => 'Almacén interno',
            'cliente'   => 'Cliente',
            'proveedor' => 'Proveedor',
            default     => 'Dirección libre',
        };
    }

    public function getDestinatarioNombreAttribute(): string
    {
        return match($this->tipo_destino) {
            'almacen'   => $this->almacenDestino?->nombre ?? '—',
            'cliente'   => $this->cliente ? trim($this->cliente->nombre . ' ' . $this->cliente->apellido) : '—',
            'proveedor' => $this->proveedor?->razon_social ?? '—',
            default     => $this->direccion_llegada ?? '—',
        };
    }

    // ── Helpers ───────────────────────────────────────────────────

    public function puedeAnular(): bool
    {
        return in_array($this->estado, ['pendiente', 'en_transito']);
    }

    public function puedeConfirmar(): bool
    {
        return in_array($this->estado, ['pendiente', 'en_transito']);
    }

    public function puedeEnviarSunat(): bool
    {
        return in_array($this->estado, ['pendiente', 'en_transito', 'entregada'])
            && in_array($this->sunat_estado, ['no_enviado', 'error', 'rechazado']);
    }

    public function getSunatEstadoLabelAttribute(): string
    {
        return match($this->sunat_estado) {
            'no_enviado' => 'No enviado',
            'enviado'    => 'Enviado',
            'aceptado'   => 'Aceptado',
            'rechazado'  => 'Rechazado',
            'error'      => 'Error',
            default      => 'No enviado',
        };
    }

    public function getSunatEstadoCssAttribute(): string
    {
        return match($this->sunat_estado) {
            'aceptado'   => 'bg-green-100 text-green-700',
            'enviado'    => 'bg-blue-100 text-blue-700',
            'rechazado'  => 'bg-red-100 text-red-700',
            'error'      => 'bg-red-100 text-red-600',
            default      => 'bg-gray-100 text-gray-500',
        };
    }
}
