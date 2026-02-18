<?php

namespace App\Console\Commands;

use App\Services\CodigoBarrasService;
use Illuminate\Console\Command;

class GenerarCodigosBarras extends Command
{
    protected $signature = 'productos:generar-codigos 
                            {--tipo= : Tipo de producto específico}
                            {--force : Regenerar códigos existentes}';
    
    protected $description = 'Genera códigos de barras para productos sin código';
    
    public function handle(CodigoBarrasService $service)
    {
        $this->info('Generando códigos de barras...');
        
        $generados = $service->sincronizarProductosSinCodigo();
        
        $this->info("Se generaron {$generados} códigos de barras.");
        
        // Mostrar estadísticas
        $stats = $service->obtenerEstadisticas();
        $this->table(
            ['Métrica', 'Valor'],
            [
                ['Total productos', $stats['total_productos']],
                ['Con código', $stats['con_codigo']],
                ['Sin código', $stats['sin_codigo']],
                ['Cobertura', $stats['porcentaje_cobertura'] . '%'],
            ]
        );
    }
}