<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Role;

class AdminUserSeeder extends Seeder
{
    /**
     * Crea el usuario administrador principal del sistema.
     * Idempotente: usa updateOrCreate por email.
     *
     * Configurar en .env:
     *   ADMIN_NAME="Administrador"
     *   ADMIN_EMAIL="admin@tuempresa.com"
     *   ADMIN_PASSWORD="cambiar_en_produccion"
     */
    public function run(): void
    {
        $adminRole = Role::where('nombre', 'Administrador')->first();

        if (!$adminRole) {
            $this->command->error('❌ Rol Administrador no encontrado. Ejecuta RoleSeeder primero.');
            return;
        }

        $email    = env('ADMIN_EMAIL', 'admin@importaciones.com');
        $nombre   = env('ADMIN_NAME', 'Administrador');
        $password = env('ADMIN_PASSWORD', 'Admin1234!');

        $user = User::updateOrCreate(
            ['email' => $email],
            [
                'name'     => $nombre,
                'password' => Hash::make($password),
                'role_id'  => $adminRole->id,
                'estado'   => 'activo',
            ]
        );

        if ($user->wasRecentlyCreated) {
            $this->command->info("✅ Usuario admin creado: {$email}");
        } else {
            $this->command->info("✅ Usuario admin ya existe, datos actualizados: {$email}");
        }

        if (app()->environment(['local', 'development', 'testing'])) {
            $this->command->warn("   Contraseña: {$password}");
        }
    }
}
