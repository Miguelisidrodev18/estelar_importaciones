<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('roles')->insert([
            [
                'nombre' => 'Administrador',
                'descripcion' => 'Acceso total al sistema. Puede gestionar usuarios, inventario, compras, ventas y reportes.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Vendedor',
                'descripcion' => 'Gestión de ventas y clientes. Puede crear ventas, ver inventario y gestionar clientes.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Almacenero',
                'descripcion' => 'Gestión de inventario y almacenes. Puede gestionar productos, stock y movimientos de inventario.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Proveedor',
                'descripcion' => 'Acceso externo limitado. Puede ver sus compras y actualizar catálogo de productos.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Tienda',
                'descripcion' => 'Encargado de tienda - gestiona cobros y ventas del punto de venta',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        echo "✅ Roles creados exitosamente.\n";
    }
}