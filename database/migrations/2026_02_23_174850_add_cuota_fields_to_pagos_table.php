<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pagos', function (Blueprint $table) {
            // Verificar cada columna antes de agregarla
            if (!Schema::hasColumn('pagos', 'numero_cuota')) {
                $table->integer('numero_cuota')->nullable()->after('monto');
            }
            
            if (!Schema::hasColumn('pagos', 'total_cuotas')) {
                $table->integer('total_cuotas')->nullable()->after('numero_cuota');
            }
            
            if (!Schema::hasColumn('pagos', 'comprobante_path')) {
                $table->string('comprobante_path', 255)->nullable()->after('observaciones');
            }
            
            if (!Schema::hasColumn('pagos', 'comprobante_original_name')) {
                $table->string('comprobante_original_name', 255)->nullable()->after('comprobante_path');
            }
        });
    }

    public function down(): void
    {
        Schema::table('pagos', function (Blueprint $table) {
            $columns = ['numero_cuota', 'total_cuotas', 'comprobante_path', 'comprobante_original_name'];
            
            foreach ($columns as $column) {
                if (Schema::hasColumn('pagos', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};