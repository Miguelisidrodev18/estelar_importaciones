<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            // Eliminar columnas string antiguas
            $table->dropColumn(['marca', 'modelo']);

            // Agregar FKs reales al catálogo
            $table->foreignId('marca_id')->nullable()->after('categoria_id')
                  ->constrained('marcas')->nullOnDelete();

            $table->foreignId('modelo_id')->nullable()->after('marca_id')
                  ->constrained('modelos')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            $table->dropForeign(['marca_id']);
            $table->dropForeign(['modelo_id']);
            $table->dropColumn(['marca_id', 'modelo_id']);

            $table->string('marca', 100)->nullable()->after('categoria_id');
            $table->string('modelo', 100)->nullable()->after('marca');
        });
    }
};
