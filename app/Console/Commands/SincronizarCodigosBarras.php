<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Producto;

class SincronizarCodigosBarras extends Command
{
    protected $signature = 'sincronizar:codigos-barras';
    protected $description = 'Sincroniza códigos de barras de productos con la tabla múltiple';

    public function handle()
    {
        $productos = Producto::whereNotNull('codigo_barras')->get();
        $contador = 0;
        
        foreach ($productos as $producto) {
            // Verificar si ya tiene el código principal
            $existe = $producto->codigosBarras()
                ->where('codigo_barras', $producto->codigo_barras)
                ->exists();
            
            if (!$existe) {
                $producto->codigosBarras()->create([
                    'codigo_barras' => $producto->codigo_barras,
                    'descripcion' => 'Principal (sincronizado)',
                    'es_principal' => true
                ]);
                $contador++;
                $this->info("Código agregado para: {$producto->nombre}");
            }
        }
        
        $this->info("Sincronización completada. {$contador} códigos agregados.");
    }
}