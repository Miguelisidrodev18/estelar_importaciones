<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('compras', function (Blueprint $table) {
            if (!Schema::hasColumn('compras', 'tipo_compra')) {
                $table->enum('tipo_compra', ['local', 'nacional', 'importacion'])->default('local')->after('observaciones');
            }
            if (!Schema::hasColumn('compras', 'numero_dua')) {
                $table->string('numero_dua', 50)->nullable()->after('tipo_compra');
            }
            if (!Schema::hasColumn('compras', 'numero_manifiesto')) {
                $table->string('numero_manifiesto', 50)->nullable()->after('numero_dua');
            }
            if (!Schema::hasColumn('compras', 'flete')) {
                $table->decimal('flete', 10, 2)->default(0)->after('numero_manifiesto');
            }
            if (!Schema::hasColumn('compras', 'seguro')) {
                $table->decimal('seguro', 10, 2)->default(0)->after('flete');
            }
            if (!Schema::hasColumn('compras', 'otros_gastos')) {
                $table->decimal('otros_gastos', 10, 2)->default(0)->after('seguro');
            }
        });
    }

    public function down(): void
    {
        Schema::table('compras', function (Blueprint $table) {
            $columns = ['tipo_compra', 'numero_dua', 'numero_manifiesto', 'flete', 'seguro', 'otros_gastos'];
            foreach ($columns as $col) {
                if (Schema::hasColumn('compras', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
