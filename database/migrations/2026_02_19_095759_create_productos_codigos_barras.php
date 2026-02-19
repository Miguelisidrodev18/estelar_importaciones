<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('productos_codigos_barras', function (Blueprint $table) {
            $table->id();
            $table->foreignId('producto_id')->constrained('productos')->onDelete('cascade');
            $table->string('codigo_barras', 100)->unique();
            $table->string('descripcion', 100)->nullable(); // ej: "Unidad", "Caja x6", "Pack"
            $table->boolean('es_principal')->default(false);
            $table->timestamps();
            
            $table->index('producto_id');
            $table->index('codigo_barras');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('productos_codigos_barras');
    }
};