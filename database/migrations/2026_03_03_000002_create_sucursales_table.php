<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('sucursales')) {
            Schema::create('sucursales', function (Blueprint $table) {
                $table->id();
                $table->string('codigo', 10)->unique();   // S001, S002…
                $table->string('nombre', 150);
                $table->string('direccion', 300)->nullable();
                $table->string('ubigeo', 6)->nullable();
                $table->string('departamento', 100)->nullable();
                $table->string('provincia', 100)->nullable();
                $table->string('distrito', 100)->nullable();
                $table->string('telefono', 20)->nullable();
                $table->string('email', 150)->nullable();
                $table->foreignId('almacen_id')->nullable()->constrained('almacenes')->onDelete('set null');
                $table->boolean('es_principal')->default(false);
                $table->enum('estado', ['activo', 'inactivo'])->default('activo');
                $table->timestamps();

                $table->index('estado');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('sucursales');
    }
};
