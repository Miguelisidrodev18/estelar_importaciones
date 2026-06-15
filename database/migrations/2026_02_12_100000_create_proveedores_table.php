<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('proveedores', function (Blueprint $table) {
            $table->id();
            $table->string('ruc', 11)->unique();
            $table->string('razon_social');
            $table->string('nombre_comercial')->nullable();
            $table->string('direccion')->nullable();
            $table->string('telefono', 20)->nullable();
            $table->string('email')->nullable();
            $table->string('contacto_nombre')->nullable();
            $table->enum('estado', ['activo', 'inactivo'])->default('activo');
            $table->timestamps();

            $table->index('ruc');
            $table->index('razon_social');
        });
    }

    public function down(): void
    {
        // Limpiar FKs huérfanas de otras tablas que referencian proveedores
        $refs = DB::select("SELECT TABLE_NAME, CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE
            WHERE REFERENCED_TABLE_NAME='proveedores' AND TABLE_SCHEMA=DATABASE()");
        foreach ($refs as $ref) {
            try {
                DB::statement("ALTER TABLE `{$ref->TABLE_NAME}` DROP FOREIGN KEY `{$ref->CONSTRAINT_NAME}`");
            } catch (\Exception $e) {}
        }
        Schema::dropIfExists('proveedores');
    }
};
