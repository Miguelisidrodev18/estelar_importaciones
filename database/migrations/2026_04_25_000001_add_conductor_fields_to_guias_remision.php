<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('guias_remision', function (Blueprint $table) {
            $table->string('conductor_dni', 8)->nullable()->after('transportista_nombre');
            $table->string('conductor_nombre', 200)->nullable()->after('conductor_dni');
            $table->string('conductor_licencia', 20)->nullable()->after('conductor_nombre');
            $table->string('placa_vehiculo', 20)->nullable()->after('conductor_licencia');
        });
    }

    public function down(): void
    {
        Schema::table('guias_remision', function (Blueprint $table) {
            $table->dropColumn(['conductor_dni', 'conductor_nombre', 'conductor_licencia', 'placa_vehiculo']);
        });
    }
};
