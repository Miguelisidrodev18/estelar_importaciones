<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clientes', function (Blueprint $table) {
            $table->id();
            $table->enum('tipo_documento', ['DNI', 'RUC', 'CE'])->default('DNI');
            $table->string('numero_documento', 11)->unique();
            $table->string('nombre');
            $table->string('direccion')->nullable();
            $table->string('telefono', 20)->nullable();
            $table->string('email')->nullable();
            $table->enum('estado', ['activo', 'inactivo'])->default('activo');
            $table->timestamps();

            $table->index('numero_documento');
            $table->index('nombre');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clientes');
    }
};
