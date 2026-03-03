<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('empresas')) {
            Schema::create('empresas', function (Blueprint $table) {
                $table->id();
                $table->string('ruc', 11)->unique();
                $table->string('razon_social', 200);
                $table->string('nombre_comercial', 200)->nullable();
                $table->string('direccion', 300)->nullable();
                $table->string('ubigeo', 6)->nullable();
                $table->string('departamento', 100)->nullable();
                $table->string('provincia', 100)->nullable();
                $table->string('distrito', 100)->nullable();
                $table->enum('regimen', ['RER', 'RG', 'RMT', 'RUS'])->default('RG');
                $table->string('telefono', 20)->nullable();
                $table->string('email', 150)->nullable();
                $table->string('web', 200)->nullable();
                // Redes sociales
                $table->string('facebook', 200)->nullable();
                $table->string('instagram', 200)->nullable();
                $table->string('tiktok', 200)->nullable();
                // Logos
                $table->string('logo_path', 300)->nullable();
                $table->string('logo_pdf_path', 300)->nullable();
                // Config SUNAT / API REST
                $table->string('sunat_usuario_sol', 100)->nullable();
                $table->string('sunat_clave_sol', 100)->nullable();
                $table->string('sunat_token', 500)->nullable();
                $table->enum('sunat_modo', ['beta', 'produccion'])->default('beta');
                $table->string('api_url', 300)->nullable();
                $table->string('api_key', 300)->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('empresas');
    }
};
