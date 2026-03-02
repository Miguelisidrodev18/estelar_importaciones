<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('producto_precios', function (Blueprint $table) {
            // Precio de compra (costo) y precio mayorista
            $table->decimal('precio_compra', 12, 2)->nullable()->after('precio');
            $table->decimal('precio_mayorista', 12, 2)->nullable()->after('precio_compra');
            $table->decimal('margen', 5, 2)->nullable()->after('precio_mayorista');
            $table->text('observaciones')->nullable()->after('margen');

            // Soporte de variante (null = aplica al producto base)
            $table->foreignId('variante_id')
                  ->nullable()
                  ->after('proveedor_id')
                  ->constrained('producto_variantes')
                  ->onDelete('cascade');

            // Soporte de tienda/almacén (null = precio global para todas)
            $table->foreignId('almacen_id')
                  ->nullable()
                  ->after('variante_id')
                  ->constrained('almacenes')
                  ->onDelete('cascade');

            $table->index(['producto_id', 'almacen_id', 'tipo_precio', 'activo'], 'idx_pp_almacen_tipo');
            $table->index(['variante_id', 'almacen_id', 'activo'], 'idx_pp_variante_almacen');
        });
    }

    public function down(): void
    {
        Schema::table('producto_precios', function (Blueprint $table) {
            $table->dropIndex('idx_pp_almacen_tipo');
            $table->dropIndex('idx_pp_variante_almacen');
            $table->dropForeign(['variante_id']);
            $table->dropForeign(['almacen_id']);
            $table->dropColumn(['precio_compra', 'precio_mayorista', 'margen', 'observaciones', 'variante_id', 'almacen_id']);
        });
    }
};
