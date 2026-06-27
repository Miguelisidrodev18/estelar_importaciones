<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('guias_remision', function (Blueprint $table) {
            $table->string('sunat_estado', 30)->default('no_enviado')->after('estado');
            $table->unsignedBigInteger('sunat_api_id')->nullable()->after('sunat_estado');
            $table->string('sunat_ticket', 50)->nullable()->after('sunat_api_id');
            $table->string('sunat_cdr_code', 10)->nullable()->after('sunat_ticket');
            $table->text('sunat_descripcion')->nullable()->after('sunat_cdr_code');
            $table->timestamp('sunat_enviado_at')->nullable()->after('sunat_descripcion');
        });
    }

    public function down(): void
    {
        Schema::table('guias_remision', function (Blueprint $table) {
            $table->dropColumn([
                'sunat_estado', 'sunat_api_id', 'sunat_ticket',
                'sunat_cdr_code', 'sunat_descripcion', 'sunat_enviado_at',
            ]);
        });
    }
};
