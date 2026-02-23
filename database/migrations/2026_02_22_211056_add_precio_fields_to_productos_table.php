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
        Schema::table('productos', function (Blueprint $table) {
            if (!Schema::hasColumn('productos', 'ultimo_costo_compra')) {
                $table->decimal('ultimo_costo_compra', 12, 2)->nullable()->after('stock_maximo');
            }
            if (!Schema::hasColumn('productos', 'costo_promedio')) {
                $table->decimal('costo_promedio', 12, 2)->nullable()->after('ultimo_costo_compra');
            }
            if (!Schema::hasColumn('productos', 'fecha_ultima_compra')) {
                $table->date('fecha_ultima_compra')->nullable()->after('costo_promedio');
            }
        });
    }

    public function down(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            $cols = ['ultimo_costo_compra', 'costo_promedio', 'fecha_ultima_compra'];
            foreach ($cols as $col) {
                if (Schema::hasColumn('productos', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
