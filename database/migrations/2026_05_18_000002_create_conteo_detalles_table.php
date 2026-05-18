<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('conteo_detalles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conteo_id')->constrained('conteos_inventario')->cascadeOnDelete();
            $table->foreignId('producto_id')->constrained('productos');
            $table->foreignId('variante_id')->nullable()->constrained('producto_variantes')->nullOnDelete();
            $table->integer('stock_sistema')->default(0);
            $table->integer('stock_fisico')->nullable();
            $table->timestamp('contado_at')->nullable();
            $table->text('observaciones')->nullable();
            $table->timestamps();
            $table->unique(['conteo_id', 'producto_id', 'variante_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conteo_detalles');
    }
};
