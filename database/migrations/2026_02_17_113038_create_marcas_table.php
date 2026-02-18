<?php
// database/migrations/xxxx_create_marcas_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('marcas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);
            $table->string('logo')->nullable();
            $table->text('descripcion')->nullable();
            $table->string('sitio_web')->nullable();
            $table->enum('estado', ['activo', 'inactivo'])->default('activo');
            $table->timestamps();
            
            $table->unique('nombre');
        });
    }

    public function down()
    {
        Schema::dropIfExists('marcas');
    }
};