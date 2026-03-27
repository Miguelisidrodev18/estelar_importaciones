<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guias_remision', function (Blueprint $table) {
            $table->id();
            $table->foreignId('venta_id')->constrained('ventas')->onDelete('cascade');
            $table->string('motivo_traslado', 50)->default('VENTA');
            $table->enum('modalidad', ['privado', 'publico'])->default('privado');
            $table->date('fecha_traslado');
            $table->decimal('peso_total', 8, 2)->nullable();
            $table->unsignedSmallInteger('bultos')->nullable();
            $table->string('direccion_partida', 300)->nullable();
            $table->string('ubigeo_partida', 6)->nullable();
            $table->string('direccion_llegada', 300)->nullable();
            $table->string('ubigeo_llegada', 6)->nullable();
            $table->string('transportista_tipo_doc', 10)->nullable();
            $table->string('transportista_doc', 15)->nullable();
            $table->string('transportista_nombre', 200)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guias_remision');
    }
};
