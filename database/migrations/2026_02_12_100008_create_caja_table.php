<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('caja', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('restrict');
            $table->foreignId('almacen_id')->constrained('almacenes')->onDelete('restrict');
            $table->date('fecha');
            $table->decimal('monto_inicial', 10, 2);
            $table->decimal('monto_final', 10, 2)->default(0);
            $table->enum('estado', ['abierta', 'cerrada'])->default('abierta');
            $table->timestamps();

            $table->index(['user_id', 'estado']);
            $table->index('almacen_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('caja');
    }
};
