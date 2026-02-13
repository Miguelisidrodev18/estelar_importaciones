<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pedidos', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 20)->unique();
            $table->foreignId('proveedor_id')->constrained('proveedores')->onDelete('restrict');
            $table->foreignId('user_id')->constrained('users')->onDelete('restrict');
            $table->date('fecha');
            $table->date('fecha_esperada')->nullable();
            $table->enum('estado', ['pendiente', 'aprobado', 'recibido', 'cancelado'])->default('pendiente');
            $table->text('observaciones')->nullable();
            $table->timestamps();

            $table->index('proveedor_id');
            $table->index('user_id');
            $table->index('estado');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pedidos');
    }
};
