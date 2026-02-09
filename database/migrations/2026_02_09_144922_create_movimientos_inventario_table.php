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
        Schema::create('movimientos_inventario', function (Blueprint $table) {
            $table->id();
            $table->foreignId('producto_id')->constrained('productos')->onDelete('cascade');
            $table->foreignId('almacen_id')->constrained('almacenes')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('restrict');
            
            $table->enum('tipo_movimiento', [
                'ingreso',        // Entrada de mercadería
                'salida',         // Salida de mercadería
                'ajuste',         // Ajuste manual
                'transferencia',  // Entre almacenes
                'devolucion',     // Devolución de cliente/proveedor
                'merma',          // Pérdida/deterioro
            ]);
            
            $table->integer('cantidad');
            $table->integer('stock_anterior');
            $table->integer('stock_nuevo');
            
            $table->string('motivo')->nullable();
            $table->text('observaciones')->nullable();
            $table->string('documento_referencia', 50)->nullable(); // Número de compra, venta, etc.
            
            // Para transferencias entre almacenes
            $table->foreignId('almacen_destino_id')->nullable()->constrained('almacenes')->onDelete('set null');
            
            $table->timestamps();
            
            // Índices
            $table->index('producto_id');
            $table->index('almacen_id');
            $table->index('tipo_movimiento');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('movimientos_inventario');
    }
};