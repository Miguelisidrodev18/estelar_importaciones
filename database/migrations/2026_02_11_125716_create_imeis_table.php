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
        Schema::create('imeis', function (Blueprint $table) {
            $table->id();
            $table->string('codigo_imei', 20)->unique()->comment('Código IMEI único del celular');
            $table->string('serie', 50)->nullable()->comment('Número de serie');
            $table->string('color', 50)->nullable()->comment('Color del celular');
            
            // Relaciones
            $table->foreignId('producto_id')->constrained('productos')->onDelete('cascade');
            $table->foreignId('almacen_id')->nullable()->constrained('almacenes')->onDelete('set null');
            
            // Estado del IMEI
            $table->enum('estado', ['disponible', 'vendido', 'reservado', 'dañado', 'garantia'])->default('disponible');
            
            // Timestamps
            $table->timestamps();
            
            // Índices
            $table->index('producto_id');
            $table->index('almacen_id');
            $table->index('estado');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('imeis');
    }
};