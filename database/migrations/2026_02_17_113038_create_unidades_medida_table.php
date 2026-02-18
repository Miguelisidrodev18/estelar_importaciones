<?php
// database/migrations/xxxx_create_unidades_medida_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('unidades_medida', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);
            $table->string('abreviatura', 20)->unique();
            $table->enum('categoria', ['unidad', 'peso', 'volumen', 'longitud', 'otros'])->default('unidad');
            $table->text('descripcion')->nullable();
            $table->boolean('permite_decimales')->default(false);
            $table->enum('estado', ['activo', 'inactivo'])->default('activo');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('unidades_medida');
    }
};