<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Http\Controllers\RolPermisoController;
use Spatie\Permission\Models\Role;

class PermisoSeeder extends Seeder
{
    public function run(): void
    {
        RolPermisoController::sincronizarPermisos();

        // Permisos iniciales del empleado (equivalentes al acceso que tenía antes)
        $empleado = Role::findByName('empleado');
        if ($empleado->permissions()->count() === 0) {
            $empleado->syncPermissions([
                'categorias.ver', 'categorias.crear', 'categorias.editar', 'categorias.eliminar',
                'trajes.ver', 'trajes.crear', 'trajes.editar', 'trajes.eliminar',
                'clientes.ver', 'clientes.crear', 'clientes.editar', 'clientes.eliminar',
                'alquileres.ver', 'alquileres.crear', 'alquileres.editar', 'alquileres.eliminar',
                'devoluciones.crear',
            ]);
        }
    }
}
