<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ventas', function (Blueprint $table) {
            $table->boolean('es_credito')->default(false)->after('total');
            $table->enum('condicion_pago', ['contado', 'credito'])->default('contado')->after('es_credito');
        });

        // Extender el enum estado_pago para incluir 'anulado', 'cotizacion' y 'credito'
        // (algunos ya pueden existir en migraciones anteriores, pero usamos MODIFY para garantizar)
        DB::statement("ALTER TABLE ventas MODIFY COLUMN estado_pago ENUM('pendiente','pagado','cancelado','anulado','cotizacion','credito') DEFAULT 'pendiente'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE ventas MODIFY COLUMN estado_pago ENUM('pendiente','pagado','cancelado','anulado','cotizacion') DEFAULT 'pendiente'");

        Schema::table('ventas', function (Blueprint $table) {
            $table->dropColumn(['es_credito', 'condicion_pago']);
        });
    }
};
