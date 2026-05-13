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
        Schema::table('clientes', function (Blueprint $table) {
            $table->string('distrito', 100)->nullable()->after('direccion');
            $table->string('provincia', 100)->nullable()->after('distrito');
            $table->string('departamento', 100)->nullable()->after('provincia');
        });
    }

    public function down(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            $table->dropColumn(['distrito', 'provincia', 'departamento']);
        });
    }
};
