<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('empresas', function (Blueprint $table) {
            $table->string('certificado_pem_path', 300)->nullable()->after('certificado_pfx_password');
            $table->string('gre_client_id', 200)->nullable()->after('certificado_pem_path');
            $table->string('gre_client_secret', 200)->nullable()->after('gre_client_id');
        });
    }

    public function down(): void
    {
        Schema::table('empresas', function (Blueprint $table) {
            $table->dropColumn(['certificado_pem_path', 'gre_client_id', 'gre_client_secret']);
        });
    }
};
