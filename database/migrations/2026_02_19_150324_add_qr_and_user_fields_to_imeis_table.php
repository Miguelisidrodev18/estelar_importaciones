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
        Schema::table('imeis', function (Blueprint $table) {
            // Verificar si la columna 'observaciones' existe, si no, crearla primero
            if (!Schema::hasColumn('imeis', 'observaciones')) {
                $table->text('observaciones')->nullable()->after('fecha_venta');
            }
            
            // Agregar qr_path DESPUÉS de fecha_venta (no después de observaciones)
            if (!Schema::hasColumn('imeis', 'qr_path')) {
                $table->string('qr_path')->nullable()->after('fecha_venta');
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
                      ->after('fecha_venta');
            }
            
            // Crear índices para búsquedas frecuentes
            $this->crearIndicesSiNoExisten($table);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('imeis', function (Blueprint $table) {
            // Eliminar índices primero
            $this->eliminarIndicesSiExisten($table);
            
            // Eliminar columnas
            if (Schema::hasColumn('imeis', 'usuario_registro_id')) {
                $table->dropForeign(['usuario_registro_id']);
                $table->dropColumn('usuario_registro_id');
            }
            
            if (Schema::hasColumn('imeis', 'qr_path')) {
                $table->dropColumn('qr_path');
            }
            
            if (Schema::hasColumn('imeis', 'fecha_garantia')) {
                $table->dropColumn('fecha_garantia');
            }
            
            // NOTA: No eliminamos 'observaciones' por si ya existía antes
        });
    }
    
    /**
     * Crear índices si no existen
     */
    private function crearIndicesSiNoExisten(Blueprint $table): void
    {
        $sm = Schema::getConnection()->getDoctrineSchemaManager();
        $indexes = $sm->listTableIndexes('imeis');
        
        if (!array_key_exists('imeis_estado_imei_index', $indexes)) {
            $table->index('estado_imei');
        }
        
        if (!array_key_exists('imeis_fecha_ingreso_index', $indexes)) {
            $table->index('fecha_ingreso');
        }
        
        if (!array_key_exists('imeis_fecha_garantia_index', $indexes)) {
            $table->index('fecha_garantia');
        }
    }
    
    /**
     * Eliminar índices si existen
     */
    private function eliminarIndicesSiExisten(Blueprint $table): void
    {
        $sm = Schema::getConnection()->getDoctrineSchemaManager();
        $indexes = $sm->listTableIndexes('imeis');
        
        if (array_key_exists('imeis_estado_imei_index', $indexes)) {
            $table->dropIndex(['estado_imei']);
        }
        
        if (array_key_exists('imeis_fecha_ingreso_index', $indexes)) {
            $table->dropIndex(['fecha_ingreso']);
        }
        
        if (array_key_exists('imeis_fecha_garantia_index', $indexes)) {
            $table->dropIndex(['fecha_garantia']);
        }
    }
};