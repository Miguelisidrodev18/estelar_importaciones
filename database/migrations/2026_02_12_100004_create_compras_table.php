<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('compras', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 20)->unique();
            $table->foreignId('proveedor_id')->constrained('proveedores')->onDelete('restrict');
            $table->foreignId('user_id')->constrained('users')->onDelete('restrict');
            $table->foreignId('almacen_id')->constrained('almacenes')->onDelete('restrict');
            $table->string('numero_factura', 50);
            $table->date('fecha');
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('igv', 10, 2)->default(0);
            $table->decimal('total', 10, 2);
            $table->enum('estado', ['registrado', 'anulado'])->default('registrado');
            $table->text('observaciones')->nullable();
            $table->timestamps();

            $table->index('proveedor_id');
            $table->index('almacen_id');
            $table->index('numero_factura');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('compras');
    }
};
