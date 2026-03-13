<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrasladoImei extends Model
{
    protected $table = 'traslado_imeis';

    protected $fillable = ['movimiento_id', 'imei_id'];

    public function movimiento()
    {
        return $this->belongsTo(MovimientoInventario::class, 'movimiento_id');
    }

    public function imei()
    {
        return $this->belongsTo(Imei::class);
    }
}
