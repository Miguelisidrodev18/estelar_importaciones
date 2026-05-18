<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GuiaRemision extends Model
{
    protected $table = 'guias_remision';

    protected $fillable = [
        'venta_id', 'proveedor_id', 'cliente_id', 'numero_guia', 'motivo_traslado', 'modalidad',
        'fecha_traslado', 'peso_total', 'bultos',
        'direccion_partida', 'ubigeo_partida',
        'direccion_llegada', 'ubigeo_llegada',
        'transportista_tipo_doc', 'transportista_doc', 'transportista_nombre',
        'conductor_dni', 'conductor_nombre', 'conductor_licencia', 'placa_vehiculo',
        'estado',
    ];

    protected $casts = [
        'fecha_traslado' => 'date',
        'peso_total'     => 'decimal:2',
    ];

    public function venta()
    {
        return $this->belongsTo(Venta::class);
    }

    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class);
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

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
            'IMPORTACION'              => 'Importación',
            'EXPORTACION'              => 'Exportación',
            default                    => 'Otros',
        };
    }

    public function getModalidadLabelAttribute(): string
    {
        return $this->modalidad === 'privado' ? 'Transporte Privado' : 'Transporte Público';
    }
}
