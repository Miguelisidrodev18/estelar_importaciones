<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('imeis', function (Blueprint $table) {
            // Verificar si la columna 'observaciones' existe, si no, crearla primero
            if (!Schema::hasColumn('imeis', 'observaciones')) {
                $table->text('observaciones')->nullable();
            }
            
            // Agregar qr_path DESPUÉS de fecha_venta (no después de observaciones)
            if (!Schema::hasColumn('imeis', 'qr_path')) {
                $table->string('qr_path')->nullable();
            }
            
            // Agregar usuario_registro_id
            if (!Schema::hasColumn('imeis', 'usuario_registro_id')) {
                $table->foreignId('usuario_registro_id')
                      ->nullable()
                      ->after('qr_path')
                      ->constrained('users')
                      ->nullOnDelete();
            }
            
            // Agregar fecha_garantia
            if (!Schema::hasColumn('imeis', 'fecha_garantia')) {
                $table->date('fecha_garantia')
                      ->nullable()
                      ;
            }
            
            // Crear índices para búsquedas frecuentes

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Eliminar índices opcionales (creados fuera de este migration si existen)
        $idxNames = array_unique(array_column(DB::select('SHOW INDEX FROM imeis'), 'Key_name'));
        foreach (['imeis_estado_imei_index' => ['estado_imei'], 'imeis_fecha_ingreso_index' => ['fecha_ingreso'], 'imeis_fecha_garantia_index' => ['fecha_garantia']] as $idxName => $cols) {
            if (in_array($idxName, $idxNames)) {
                Schema::table('imeis', fn($t) => $t->dropIndex($cols));
            }
        }

        // Soltar FK separado del dropColumn para evitar error de MySQL
        if (Schema::hasColumn('imeis', 'usuario_registro_id')) {
            Schema::table('imeis', fn($t) => $t->dropForeign(['usuario_registro_id']));
            Schema::table('imeis', fn($t) => $t->dropColumn('usuario_registro_id'));
        }

        Schema::table('imeis', function (Blueprint $table) {
            foreach (['qr_path', 'fecha_garantia'] as $col) {
                if (Schema::hasColumn('imeis', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};