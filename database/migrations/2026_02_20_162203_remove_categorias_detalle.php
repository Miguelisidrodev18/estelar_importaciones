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
        schema::table('categorias', function (Blueprint $table) {
            $table->dropColumn('descripcion');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        schema::table('categorias', function (Blueprint $table) {
            $table->text('descripcion')->nullable();
        });
    }
};
