<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ventas', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 20)->unique();
            $table->foreignId('user_id')->constrained('users')->onDelete('restrict');
            $table->foreignId('cliente_id')->nullable()->constrained('clientes')->onDelete('set null');
            $table->foreignId('almacen_id')->constrained('almacenes')->onDelete('restrict');
            $table->date('fecha');
            $table->decimal('subtotal', 10, 2);
            $table->decimal('igv', 10, 2)->default(0);
            $table->decimal('total', 10, 2);
            $table->enum('metodo_pago', ['efectivo', 'transferencia', 'yape', 'plin'])->nullable();
            $table->enum('estado_pago', ['pendiente', 'pagado', 'cancelado'])->default('pendiente');
            $table->foreignId('usuario_confirma_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('fecha_confirmacion')->nullable();
            $table->text('observaciones')->nullable();
            $table->timestamps();

            $table->index('user_id');
            $table->index('almacen_id');
            $table->index('estado_pago');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ventas');
    }
};
