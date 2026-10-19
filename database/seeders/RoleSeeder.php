<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        Role::firstOrCreate(['name' => 'administrador', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'empleado', 'guard_name' => 'web']);

        // Crea todos los permisos (incluye bitacora.ver, backup.ver, backup.generar)
        // y asigna al administrador todos ellos
        $this->call(PermisoSeeder::class);
    }
}
