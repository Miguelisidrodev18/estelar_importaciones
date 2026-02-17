<?php
// database/migrations/xxxx_add_almacen_id_to_users_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            // Agregar campo almacen_id (nullable porque algunos roles no necesitan almacén)
            $table->foreignId('almacen_id')
                  ->nullable()
                  ->after('role_id')
                  ->constrained('almacenes')
                  ->nullOnDelete();
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['almacen_id']);
            $table->dropColumn('almacen_id');
        });
    }
};