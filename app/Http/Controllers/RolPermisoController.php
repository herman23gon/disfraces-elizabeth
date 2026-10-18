<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolPermisoController extends Controller
{
    public const MODULOS = [
        'categorias' => 'Categorías',
        'trajes' => 'Trajes',
        'clientes' => 'Clientes',
        'alquileres' => 'Alquileres',
        'reportes' => 'Reportes',
    ];

    public const ACCIONES = [
        'ver' => 'Ver',
        'crear' => 'Crear',
        'editar' => 'Editar',
        'eliminar' => 'Eliminar',
    ];

    /**
     * Lista de todos los nombres de permisos (ej: categorias.ver)
     */
    public static function nombresPermisos(): array
    {
        $permisos = [];
        foreach (array_keys(self::MODULOS) as $modulo) {
            foreach (array_keys(self::ACCIONES) as $accion) {
                $permisos[] = $modulo . '.' . $accion;
            }
        }
        return $permisos;
    }

    /**
     * Crea los permisos que falten y asegura que el administrador los tenga todos
     */
    public static function sincronizarPermisos(): void
    {
        foreach (self::nombresPermisos() as $nombre) {
            Permission::firstOrCreate(['name' => $nombre, 'guard_name' => 'web']);
        }

        $admin = Role::firstOrCreate(['name' => 'administrador', 'guard_name' => 'web']);
        $admin->syncPermissions(self::nombresPermisos());

        Role::firstOrCreate(['name' => 'empleado', 'guard_name' => 'web']);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function index()
    {
        self::sincronizarPermisos();

        $empleado = Role::findByName('empleado');
        $permisosEmpleado = $empleado->permissions->pluck('name')->toArray();

        return view('roles.index', [
            'modulos' => self::MODULOS,
            'acciones' => self::ACCIONES,
            'permisosEmpleado' => $permisosEmpleado,
        ]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'permisos' => 'nullable|array',
            'permisos.*' => 'in:' . implode(',', self::nombresPermisos()),
        ]);

        self::sincronizarPermisos();

        $empleado = Role::findByName('empleado');
        $empleado->syncPermissions($request->input('permisos', []));

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return redirect()->route('roles-permisos.index')->with('success', 'Permisos del rol Empleado actualizados correctamente.');
    }
}
