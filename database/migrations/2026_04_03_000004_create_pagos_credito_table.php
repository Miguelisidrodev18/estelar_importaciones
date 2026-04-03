<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pagos_credito', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cuenta_por_cobrar_id')->constrained('cuentas_por_cobrar')->onDelete('cascade');
            $table->foreignId('cuota_cobro_id')->nullable()->constrained('cuotas_cobro')->nullOnDelete();
            $table->foreignId('usuario_id')->constrained('users')->onDelete('restrict');
            $table->decimal('monto', 12, 2);
            $table->date('fecha_pago');
            $table->enum('metodo_pago', ['efectivo', 'transferencia', 'yape', 'plin'])->default('efectivo');
            $table->string('referencia', 100)->nullable();
            $table->text('observaciones')->nullable();
            $table->timestamps();

            $table->index('fecha_pago');
            $table->index('cuenta_por_cobrar_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pagos_credito');
    }
};
