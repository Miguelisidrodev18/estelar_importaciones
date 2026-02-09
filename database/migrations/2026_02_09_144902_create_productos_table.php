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
        Schema::create('productos', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 50)->unique();
            $table->string('nombre', 200);
            $table->text('descripcion')->nullable();
            $table->foreignId('categoria_id')->constrained('categorias')->onDelete('restrict');
            $table->string('marca', 100)->nullable();
            $table->string('modelo', 100)->nullable();
            $table->string('unidad_medida', 20)->default('unidad'); // unidad, kg, litro, caja, etc.
            $table->string('codigo_barras', 100)->nullable()->unique();
            $table->string('imagen')->nullable();
            
            // Precios
            $table->decimal('precio_compra_actual', 10, 2)->default(0);
            $table->decimal('precio_venta', 10, 2)->default(0);
            $table->decimal('precio_mayorista', 10, 2)->nullable();
            
            // Stock
            $table->integer('stock_actual')->default(0);
            $table->integer('stock_minimo')->default(10);
            $table->integer('stock_maximo')->default(1000);
            
            // Ubicación
            $table->string('ubicacion', 50)->nullable(); // Pasillo, estante, etc.
            
            // Estado
            $table->enum('estado', ['activo', 'inactivo', 'descontinuado'])->default('activo');
            
            $table->timestamps();
            
            // Índices para búsquedas rápidas
            $table->index('codigo');
            $table->index('nombre');
            $table->index('codigo_barras');
            $table->index('categoria_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('productos');
    }
};