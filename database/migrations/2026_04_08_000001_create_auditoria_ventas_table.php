<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('auditoria_ventas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('venta_id')->nullable()->constrained('ventas')->nullOnDelete();
            $table->foreignId('usuario_id')->constrained('users')->onDelete('restrict');
            $table->enum('accion', ['editar', 'anular', 'eliminar']);
            $table->json('datos_anteriores')->nullable();
            $table->json('datos_nuevos')->nullable();
            $table->boolean('requirio_clave')->default(false);
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('created_at')->useCurrent()->index();
            // No updated_at — es un log inmutable
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('auditoria_ventas');
    }
};
