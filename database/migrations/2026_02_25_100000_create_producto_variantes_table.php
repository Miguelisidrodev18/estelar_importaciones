<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('producto_variantes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('producto_id')->constrained('productos')->onDelete('cascade');
            $table->foreignId('color_id')->nullable()->constrained('colores')->nullOnDelete();
            $table->string('capacidad', 50)->nullable()->comment('Ej: 64GB, 128GB, 256GB');
            $table->string('sku', 100)->unique()->comment('SKU generado: BASE-COLOR-CAP');
            $table->decimal('sobreprecio', 10, 2)->default(0)->comment('Precio adicional sobre el producto base');
            $table->integer('stock_actual')->default(0);
            $table->integer('stock_minimo')->default(0);
            $table->enum('estado', ['activo', 'inactivo'])->default('activo');
            $table->string('imagen', 255)->nullable();
            $table->foreignId('creado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['producto_id', 'estado']);
            $table->index(['producto_id', 'color_id', 'capacidad']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('producto_variantes');
    }
};
