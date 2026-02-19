<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('detalle_compras', function (Blueprint $table) {
            // Asegurar que existe producto_id como FK
            if (!Schema::hasColumn('detalle_compras', 'producto_id')) {
                $table->foreignId('producto_id')->after('compra_id')->constrained('productos');
            }

            // Agregar campo de texto para referencia del proveedor en esta compra
            if (!Schema::hasColumn('detalle_compras', 'codigo_proveedor_referencia')) {
                $table->string('codigo_proveedor_referencia', 100)->nullable()->after('subtotal');
            }
        });
    }

    public function down(): void
    {
        Schema::table('detalle_compras', function (Blueprint $table) {
            if (Schema::hasColumn('detalle_compras', 'codigo_proveedor_referencia')) {
                $table->dropColumn('codigo_proveedor_referencia');
            }
        });
    }
};