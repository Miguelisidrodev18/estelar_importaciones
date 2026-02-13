<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('imeis', function (Blueprint $table) {
            $table->foreignId('compra_id')->nullable()->after('almacen_id')->constrained('compras')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('imeis', function (Blueprint $table) {
            $table->dropForeign(['compra_id']);
            $table->dropColumn('compra_id');
        });
    }
};
