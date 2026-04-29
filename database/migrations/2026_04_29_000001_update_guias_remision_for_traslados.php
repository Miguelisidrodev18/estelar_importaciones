<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('guias_remision', function (Blueprint $table) {
            $table->string('numero_guia', 50)->nullable()->after('id');
            $table->unsignedBigInteger('venta_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('guias_remision', function (Blueprint $table) {
            $table->dropColumn('numero_guia');
            $table->unsignedBigInteger('venta_id')->nullable(false)->change();
        });
    }
};
