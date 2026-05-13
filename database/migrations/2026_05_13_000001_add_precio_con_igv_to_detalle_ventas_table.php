<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('detalle_ventas', function (Blueprint $table) {
            $table->decimal('precio_con_igv', 10, 2)->nullable()->after('precio_unitario');
            $table->decimal('subtotal_con_igv', 10, 2)->nullable()->after('subtotal');
        });

        // Backfill ventas con un solo detalle: usar venta.total (valor exacto original)
        DB::statement("
            UPDATE detalle_ventas dv
            JOIN ventas v ON dv.venta_id = v.id
            JOIN (
                SELECT venta_id, COUNT(*) AS cnt
                FROM detalle_ventas
                GROUP BY venta_id
            ) g ON g.venta_id = dv.venta_id
            SET
                dv.precio_con_igv   = ROUND(v.total / NULLIF(dv.cantidad, 0), 2),
                dv.subtotal_con_igv = v.total
            WHERE g.cnt = 1
              AND v.total IS NOT NULL
        ");

        // Backfill ventas con múltiples detalles: aproximación proporcional desde subtotal ex-IGV
        DB::statement("
            UPDATE detalle_ventas dv
            JOIN ventas v ON dv.venta_id = v.id
            JOIN (
                SELECT venta_id, COUNT(*) AS cnt, SUM(subtotal) AS suma_subtotal
                FROM detalle_ventas
                GROUP BY venta_id
            ) g ON g.venta_id = dv.venta_id
            SET
                dv.precio_con_igv   = ROUND(dv.precio_unitario * 1.18, 2),
                dv.subtotal_con_igv = ROUND(
                    ROUND(dv.precio_unitario * 1.18, 2) * dv.cantidad,
                2)
            WHERE g.cnt > 1
        ");
    }

    public function down(): void
    {
        Schema::table('detalle_ventas', function (Blueprint $table) {
            $table->dropColumn(['precio_con_igv', 'subtotal_con_igv']);
        });
    }
};
