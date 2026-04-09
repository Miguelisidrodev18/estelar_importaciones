<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditoriaVenta extends Model
{
    protected $table = 'auditoria_ventas';

    // Log inmutable: sin updated_at
    public $timestamps = false;
    const CREATED_AT = 'created_at';

    protected $fillable = [
        'venta_id',
        'usuario_id',
        'accion',
        'datos_anteriores',
        'datos_nuevos',
        'requirio_clave',
        'ip_address',
    ];

    protected $casts = [
        'datos_anteriores' => 'array',
        'datos_nuevos'     => 'array',
        'requirio_clave'   => 'boolean',
        'created_at'       => 'datetime',
    ];

    // ─── Relaciones ──────────────────────────────────────────────────────────────

    public function venta()
    {
        return $this->belongsTo(Venta::class)->withTrashed();
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    // ─── Scopes ──────────────────────────────────────────────────────────────────

    public function scopeAccion($query, string $accion)
    {
        return $query->where('accion', $accion);
    }
}
