<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Roles del sistema. Idempotente: se puede ejecutar múltiples veces sin duplicar.
     */
    public function run(): void
    {
        $roles = [
            [
                'nombre'      => 'Administrador',
                'descripcion' => 'Acceso total al sistema. Puede gestionar usuarios, inventario, compras, ventas y reportes.',
            ],
            [
                'nombre'      => 'Vendedor',
                'descripcion' => 'Gestión de ventas y clientes. Puede crear ventas, ver inventario y gestionar clientes.',
            ],
            [
                'nombre'      => 'Almacenero',
                'descripcion' => 'Gestión de inventario y almacenes. Puede gestionar productos, stock y movimientos de inventario.',
            ],
            [
                'nombre'      => 'Proveedor',
                'descripcion' => 'Acceso externo limitado. Puede ver sus compras y actualizar catálogo de productos.',
            ],
            [
                'nombre'      => 'Tienda',
                'descripcion' => 'Encargado de tienda - gestiona cobros y ventas del punto de venta.',
            ],
        ];

        foreach ($roles as $rol) {
            Role::firstOrCreate(
                ['nombre' => $rol['nombre']],
                ['descripcion' => $rol['descripcion']]
            );
        }

        $this->command->info('✅ Roles verificados/creados correctamente.');
    }
}
