<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            // Verificar si las columnas existen antes de eliminarlas
            $columns = ['costo_promedio', 'ultimo_costo_compra', 'fecha_ultima_compra'];
            
            foreach ($columns as $column) {
                if (Schema::hasColumn('productos', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            // Restaurar columnas en caso de rollback
            if (!Schema::hasColumn('productos', 'costo_promedio')) {
                $table->decimal('costo_promedio', 10, 2)->default(0)->after('tipo_garantia');
            }
            
            if (!Schema::hasColumn('productos', 'ultimo_costo_compra')) {
                $table->decimal('ultimo_costo_compra', 10, 2)->default(0)->after('costo_promedio');
            }
            
            if (!Schema::hasColumn('productos', 'fecha_ultima_compra')) {
                $table->date('fecha_ultima_compra')->nullable()->after('ultimo_costo_compra');
            }
        });
    }
};