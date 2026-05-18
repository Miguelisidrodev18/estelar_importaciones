<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('conteos_inventario', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->foreignId('almacen_id')->constrained('almacenes');
            $table->foreignId('user_id')->constrained('users');
            $table->enum('estado', ['activo', 'exportado'])->default('activo');
            $table->text('observaciones')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conteos_inventario');
    }
};
