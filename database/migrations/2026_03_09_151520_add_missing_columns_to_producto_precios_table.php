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
        Schema::table('producto_precios', function (Blueprint $table) {
            if (!Schema::hasColumn('producto_precios', 'variante_id')) {
                $table->foreignId('variante_id')->nullable()->after('producto_id')
                      ->constrained('producto_variantes')->onDelete('set null');
            }
            if (!Schema::hasColumn('producto_precios', 'almacen_id')) {
                $table->foreignId('almacen_id')->nullable()->after('variante_id')
                      ->constrained('almacenes')->onDelete('set null');
            }
            if (!Schema::hasColumn('producto_precios', 'precio_compra')) {
                $table->decimal('precio_compra', 10, 2)->nullable()->after('precio');
            }
            if (!Schema::hasColumn('producto_precios', 'precio_mayorista')) {
                $table->decimal('precio_mayorista', 10, 2)->nullable()->after('precio_compra');
            }
            if (!Schema::hasColumn('producto_precios', 'margen')) {
                $table->decimal('margen', 8, 2)->nullable()->after('precio_mayorista');
            }
            if (!Schema::hasColumn('producto_precios', 'observaciones')) {
                $table->text('observaciones')->nullable()->after('margen');
            }
            if (!Schema::hasColumn('producto_precios', 'creado_por')) {
                $table->unsignedBigInteger('creado_por')->nullable()->after('activo');
                $table->foreign('creado_por')->references('id')->on('users')->onDelete('set null');
            }
        });
    }

    public function down(): void
    {
        Schema::table('producto_precios', function (Blueprint $table) {
            if (Schema::hasColumn('producto_precios', 'creado_por')) {
                $table->dropForeign(['creado_por']);
                $table->dropColumn('creado_por');
            }
            foreach (['variante_id', 'almacen_id'] as $col) {
                if (Schema::hasColumn('producto_precios', $col)) {
                    $table->dropForeign([$col]);
                    $table->dropColumn($col);
                }
            }
            foreach (['precio_compra', 'precio_mayorista', 'margen', 'observaciones'] as $col) {
                if (Schema::hasColumn('producto_precios', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
