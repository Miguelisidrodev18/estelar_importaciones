<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Producto;

class CheckProductoVariantes extends Command
{
    protected $signature = 'check:variantes {producto_id=25}';
    protected $description = 'Check product variants';

    public function handle()
    {
        $productoId = $this->argument('producto_id');
        $producto = Producto::find($productoId);

        if (!$producto) {
            $this->error("Producto no encontrado");
            return 1;
        }

        $this->info("Producto: " . $producto->nombre);
        $this->line("ID: " . $producto->id);
        
        $this->line("\n=== TODAS LAS VARIANTES ===");
        $todas = $producto->variantes()->get();
        foreach ($todas as $v) {
            $this->line("  ID: {$v->id}, Capacidad: {$v->capacidad}, Color: " . ($v->color?->nombre ?? 'N/A') . ", Estado: {$v->estado}");
        }
        
        $this->line("\n=== VARIANTES ACTIVAS (usando relación) ===");
        $activas = $producto->variantesActivas()->get();
        foreach ($activas as $v) {
            $this->line("  ID: {$v->id}, Capacidad: {$v->capacidad}, Color: " . ($v->color?->nombre ?? 'N/A') . ", Estado: {$v->estado}");
        }
        
        $this->line("\n=== AGRUPADAS POR CAPACIDAD (activas) ===");
        $porCapacidad = $activas->groupBy(fn($v) => $v->capacidad ?? '');
        foreach ($porCapacidad as $cap => $vars) {
            $this->line("$cap: " . $vars->count() . " variante(s)");
            foreach ($vars as $v) {
                $this->line("    - " . ($v->color?->nombre ?? 'N/A'));
            }
        }
        
        return 0;
    }
}
