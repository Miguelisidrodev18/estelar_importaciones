<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('caja', function (Blueprint $table) {
            if (!Schema::hasColumn('caja', 'sucursal_id')) {
                $table->foreignId('sucursal_id')->nullable()->after('almacen_id')
                      ->constrained('sucursales')->onDelete('set null');
            }
            if (!Schema::hasColumn('caja', 'fecha_apertura')) {
                $table->timestamp('fecha_apertura')->nullable()->after('fecha');
            }
            if (!Schema::hasColumn('caja', 'fecha_cierre')) {
                $table->timestamp('fecha_cierre')->nullable()->after('fecha_apertura');
            }
            if (!Schema::hasColumn('caja', 'observaciones_apertura')) {
                $table->text('observaciones_apertura')->nullable()->after('estado');
            }
            if (!Schema::hasColumn('caja', 'observaciones_cierre')) {
                $table->text('observaciones_cierre')->nullable()->after('observaciones_apertura');
            }
            if (!Schema::hasColumn('caja', 'monto_real_cierre')) {
                $table->decimal('monto_real_cierre', 10, 2)->nullable()->after('monto_final');
            }
            if (!Schema::hasColumn('caja', 'diferencia_cierre')) {
                $table->decimal('diferencia_cierre', 10, 2)->nullable()->after('monto_real_cierre');
            }
        });

        Schema::table('movimientos_caja', function (Blueprint $table) {
            if (!Schema::hasColumn('movimientos_caja', 'metodo_pago')) {
                $table->enum('metodo_pago', ['efectivo', 'yape', 'plin', 'transferencia', 'mixto'])
                      ->default('efectivo')->after('tipo');
            }
            if (!Schema::hasColumn('movimientos_caja', 'referencia')) {
                $table->string('referencia', 100)->nullable()->after('concepto');
            }
            if (!Schema::hasColumn('movimientos_caja', 'user_id')) {
                $table->foreignId('user_id')->nullable()->after('caja_id')
                      ->constrained('users')->onDelete('set null');
            }
        });
    }

    public function down(): void
    {
        Schema::table('caja', function (Blueprint $table) {
            $table->dropColumn(['sucursal_id', 'fecha_apertura', 'fecha_cierre',
                'observaciones_apertura', 'observaciones_cierre',
                'monto_real_cierre', 'diferencia_cierre']);
        });
        Schema::table('movimientos_caja', function (Blueprint $table) {
            $table->dropColumn(['metodo_pago', 'referencia', 'user_id']);
        });
    }
};
