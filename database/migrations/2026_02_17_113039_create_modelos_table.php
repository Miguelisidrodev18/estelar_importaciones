<?php
// database/migrations/xxxx_create_modelos_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('modelos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);
            $table->foreignId('marca_id')->constrained('marcas')->onDelete('restrict');
            $table->foreignId('categoria_id')->nullable()->constrained('categorias')->onDelete('set null');
            $table->string('codigo_modelo', 50)->nullable();
            $table->text('especificaciones_tecnicas')->nullable();
            $table->string('imagen_referencia')->nullable();
            $table->enum('estado', ['activo', 'inactivo'])->default('activo');
            $table->timestamps();
            
            $table->unique(['marca_id', 'nombre']);
            $table->index('categoria_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('modelos');
    }
};