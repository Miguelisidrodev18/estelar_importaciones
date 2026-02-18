<?php
// database/migrations/xxxx_create_motivos_movimiento_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('motivos_movimiento', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);
            $table->string('codigo', 50)->unique()->nullable();
            $table->enum('tipo', ['ingreso', 'salida', 'transferencia', 'ajuste', 'otros'])->default('otros');
            $table->text('descripcion')->nullable();
            $table->boolean('requiere_aprobacion')->default(false);
            $table->boolean('afecta_stock')->default(true);
            $table->enum('estado', ['activo', 'inactivo'])->default('activo');
            $table->timestamps();
            
            $table->index(['tipo', 'estado']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('motivos_movimiento');
    }
};