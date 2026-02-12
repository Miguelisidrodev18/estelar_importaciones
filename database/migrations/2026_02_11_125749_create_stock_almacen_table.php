<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('stock_almacen', function (Blueprint $table) {
            $table->id();
            
            // Relaciones
            $table->foreignId('producto_id')->constrained('productos')->onDelete('cascade');
            $table->foreignId('almacen_id')->constrained('almacenes')->onDelete('cascade');
            
            // Cantidad de stock en este almacén
            $table->integer('cantidad')->default(0);
            
            // Timestamps
            $table->timestamps();
            
            // Unique: Un producto solo puede tener un registro por almacén
            $table->unique(['producto_id', 'almacen_id']);
            
            // Índices
            $table->index('producto_id');
            $table->index('almacen_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_almacen');
    }
};