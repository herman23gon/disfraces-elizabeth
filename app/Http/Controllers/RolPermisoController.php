<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\RegistrarBitacora;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolPermisoController extends Controller
{
    private const CRUD = ['ver', 'crear', 'editar', 'eliminar'];

    /**
     * Módulos y las acciones que admite cada uno
     */
    public const MODULOS = [
        'categorias' => ['nombre' => 'Categorías', 'acciones' => self::CRUD],
        'trajes' => ['nombre' => 'Trajes', 'acciones' => self::CRUD],
        'clientes' => ['nombre' => 'Clientes', 'acciones' => self::CRUD],
        'alquileres' => ['nombre' => 'Alquileres', 'acciones' => self::CRUD],
        'reportes' => ['nombre' => 'Reportes', 'acciones' => self::CRUD],
        'bitacora' => ['nombre' => 'Bitácora', 'acciones' => ['ver']],
        'backup' => ['nombre' => 'Copia de Seguridad', 'acciones' => ['ver', 'generar']],
    ];

    public const ACCIONES = [
        'ver' => 'Ver',
        'crear' => 'Crear',
        'editar' => 'Editar',
        'eliminar' => 'Eliminar',
        'generar' => 'Generar',
    ];

    /**
     * Lista de todos los nombres de permisos (ej: categorias.ver)
     */
    public static function nombresPermisos(): array
    {
        $permisos = [];
        foreach (self::MODULOS as $modulo => $datos) {
            foreach ($datos['acciones'] as $accion) {
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
        RegistrarBitacora::registrar('Editar', 'Roles y Permisos', 'Permisos del rol Empleado: ' . (implode(', ', $request->input('permisos', [])) ?: 'ninguno'));

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return redirect()->route('roles-permisos.index')->with('success', 'Permisos del rol Empleado actualizados correctamente.');
    }
}
