<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GuiaRemision extends Model
{
    protected $table = 'guias_remision';

    protected $fillable = [
        'venta_id', 'motivo_traslado', 'modalidad',
        'fecha_traslado', 'peso_total', 'bultos',
        'direccion_partida', 'ubigeo_partida',
        'direccion_llegada', 'ubigeo_llegada',
        'transportista_tipo_doc', 'transportista_doc', 'transportista_nombre',
        'conductor_dni', 'conductor_nombre', 'conductor_licencia', 'placa_vehiculo',
    ];

    protected $casts = [
        'fecha_traslado' => 'date',
        'peso_total'     => 'decimal:2',
    ];

    public function venta()
    {
        return $this->belongsTo(Venta::class);
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
