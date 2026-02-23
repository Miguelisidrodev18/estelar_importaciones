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
        Schema::table('detalle_compras', function (Blueprint $table) {
            if (!Schema::hasColumn('detalle_compras', 'descuento')) {
                $table->decimal('descuento', 5, 2)->default(0)->after('precio_unitario');
            }
        });
    }

    public function down(): void
    {
        Schema::table('detalle_compras', function (Blueprint $table) {
            if (Schema::hasColumn('detalle_compras', 'descuento')) {
                $table->dropColumn('descuento');
            }
        });
    }
};
