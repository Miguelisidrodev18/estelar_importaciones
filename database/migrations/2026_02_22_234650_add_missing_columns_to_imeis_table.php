<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('imeis', function (Blueprint $table) {
            if (!Schema::hasColumn('imeis', 'modelo_id')) {
                $table->foreignId('modelo_id')->nullable()->after('color_id')
                      ->constrained('modelos')->nullOnDelete();
            }
            if (!Schema::hasColumn('imeis', 'fecha_ingreso')) {
                $table->timestamp('fecha_ingreso')->nullable()->after('estado_imei');
            }
        });
    }

    public function down(): void
    {
        Schema::table('imeis', function (Blueprint $table) {
            if (Schema::hasColumn('imeis', 'modelo_id')) {
                $table->dropForeign(['modelo_id']);
                $table->dropColumn('modelo_id');
            }
            if (Schema::hasColumn('imeis', 'fecha_ingreso')) {
                $table->dropColumn('fecha_ingreso');
            }
        });
    }
};
