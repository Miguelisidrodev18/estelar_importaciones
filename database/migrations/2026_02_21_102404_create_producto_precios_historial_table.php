<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('producto_precios_historial', function (Blueprint $table) {
            $table->id();
            
            // Relación con producto
            $table->foreignId('producto_id')
                  ->constrained('productos')
                  ->onDelete('cascade');
            
            // Tipo de cambio
            $table->enum('tipo_cambio', [
                'venta_regular',
                'venta_mayorista',
                'venta_oferta',
                'compra'
            ])->default('venta_regular');
            
            // Valores
            $table->decimal('precio_anterior', 10, 2)->nullable();
            $table->decimal('precio_nuevo', 10, 2);
            
            // Moneda
            $table->string('moneda', 3)->default('PEN');
            
            // Motivo del cambio
            $table->text('motivo')->nullable();
            
            // Usuario que realizó el cambio
            $table->foreignId('usuario_id')
                  ->nullable()
                  ->constrained('users')
                  ->onDelete('set null');
            
            $table->timestamps();
            
            // Índices
            $table->index(['producto_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('producto_precios_historial');
    }
};