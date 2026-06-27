<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ventas', function (Blueprint $table) {
            $table->unsignedBigInteger('sunat_api_id')->nullable()->after('estado_sunat');
            $table->string('sunat_ticket', 50)->nullable()->after('sunat_api_id');
            $table->text('sunat_descripcion')->nullable()->after('sunat_ticket');
            $table->timestamp('sunat_enviado_at')->nullable()->after('sunat_descripcion');
        });

        Schema::table('empresas', function (Blueprint $table) {
            $table->string('certificado_pfx_path', 300)->nullable()->after('api_key');
            $table->string('certificado_pfx_password', 200)->nullable()->after('certificado_pfx_path');
        });
    }

    public function down(): void
    {
        Schema::table('ventas', function (Blueprint $table) {
            $table->dropColumn(['sunat_api_id', 'sunat_ticket', 'sunat_descripcion', 'sunat_enviado_at']);
        });

        Schema::table('empresas', function (Blueprint $table) {
            $table->dropColumn(['certificado_pfx_path', 'certificado_pfx_password']);
        });
    }
};
