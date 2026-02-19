<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('productos_proveedor', function (Blueprint $table) {
            $table->id();
            $table->foreignId('producto_id')->constrained('productos')->onDelete('cascade');
            $table->foreignId('proveedor_id')->constrained('proveedores')->onDelete('cascade');
            $table->string('codigo_proveedor', 100)->nullable(); // Cómo llama el proveedor al producto
            $table->decimal('ultimo_precio_compra', 10, 2)->default(0);
            $table->date('ultima_fecha_compra')->nullable();
            $table->integer('plazo_entrega_dias')->default(0);
            $table->boolean('es_preferente')->default(false);
            $table->text('observaciones')->nullable();
            $table->timestamps();
            
            // Un producto no puede estar dos veces con el mismo proveedor
            $table->unique(['producto_id', 'proveedor_id']);
            
            // Índices
            $table->index('proveedor_id');
            $table->index('producto_id');
            $table->index('codigo_proveedor');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('productos_proveedor');
    }
};