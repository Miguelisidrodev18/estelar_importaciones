<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * Seeders de producción (siempre se ejecutan):
     *   - RoleSeeder       → roles del sistema
     *   - CatalogoSeeder   → colores, marcas, unidades, motivos
     *   - AdminUserSeeder  → usuario administrador principal
     *
     * Seeders de desarrollo (solo en local/development/testing):
     *   - DevelopmentSeeder → usuarios de prueba para cada rol
     *
     * Configurar en .env antes de ejecutar en producción:
     *   ADMIN_EMAIL=admin@tuempresa.com
     *   ADMIN_NAME="Nombre Admin"
     *   ADMIN_PASSWORD=contraseña_segura
     */
    public function run(): void
    {
        // --- Datos base (producción y desarrollo) ---
        $this->call([
            RoleSeeder::class,
            CatalogoSeeder::class,
            AdminUserSeeder::class,
        ]);

        // --- Datos de prueba (solo entornos no productivos) ---
        if (app()->environment(['local', 'development', 'testing'])) {
            $this->call(DevelopmentSeeder::class);
        }
    }
}
