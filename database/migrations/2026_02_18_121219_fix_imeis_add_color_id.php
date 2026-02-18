<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('imeis', function (Blueprint $table) {
            // Reemplazar el campo color (string libre) por FK real
            $table->dropColumn('color');

            $table->foreignId('color_id')->nullable()->after('serie')
                  ->constrained('colores')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('imeis', function (Blueprint $table) {
            $table->dropForeign(['color_id']);
            $table->dropColumn('color_id');

            $table->string('color', 50)->nullable()->after('serie');
        });
    }
};
