<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('traslado_imeis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('movimiento_id')
                  ->constrained('movimientos_inventario')
                  ->onDelete('cascade');
            $table->foreignId('imei_id')
                  ->constrained('imeis')
                  ->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('traslado_imeis');
    }
};
