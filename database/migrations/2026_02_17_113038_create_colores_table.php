<?php
// database/migrations/xxxx_create_colores_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('colores', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);
            $table->string('codigo_hex', 7)->nullable(); // #FF5733
            $table->string('codigo_color', 50)->nullable(); // Código interno
            $table->text('descripcion')->nullable();
            $table->enum('estado', ['activo', 'inactivo'])->default('activo');
            $table->timestamps();
            
            $table->unique('nombre');
        });
    }

    public function down()
    {
        Schema::dropIfExists('colores');
    }
};