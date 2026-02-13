<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('movimientos_caja', function (Blueprint $table) {
            $table->id();
            $table->foreignId('caja_id')->constrained('caja')->onDelete('cascade');
            $table->foreignId('venta_id')->nullable()->constrained('ventas')->onDelete('set null');
            $table->foreignId('compra_id')->nullable()->constrained('compras')->onDelete('set null');
            $table->enum('tipo', ['ingreso', 'egreso']);
            $table->decimal('monto', 10, 2);
            $table->string('concepto');
            $table->text('observaciones')->nullable();
            $table->timestamps();

            $table->index('caja_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('movimientos_caja');
    }
};
