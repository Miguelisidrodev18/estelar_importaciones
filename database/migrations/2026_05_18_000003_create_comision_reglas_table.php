<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('comision_reglas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->enum('tipo_aplicacion', ['usuario', 'categoria', 'producto']);
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('categoria_id')->nullable()->constrained('categorias')->nullOnDelete();
            $table->foreignId('producto_id')->nullable()->constrained('productos')->nullOnDelete();
            $table->enum('tipo_calculo', ['porcentaje', 'monto_fijo']);
            $table->decimal('valor', 10, 4);
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comision_reglas');
    }
};
