<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\RegistrarBitacora;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Illuminate\Support\Facades\Hash;

class UsuarioController extends Controller
{
    public function index()
    {
        // Todos los usuarios, activos e inactivos
        $usuarios = User::with('roles')->withCount(['alquileres', 'devoluciones'])->orderBy('name')->get();
        return view('usuarios.index', compact('usuarios'));
    }

    public function create()
    {
        $roles = Role::all();
        return view('usuarios.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|exists:roles,name',
        ]);

        $usuario = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $usuario->assignRole($request->role);
        RegistrarBitacora::registrar('Crear', 'Usuarios', 'Usuario creado: ' . $usuario->name . ' (' . $usuario->email . ') con rol ' . $request->role);

        return redirect()->route('usuarios.index')->with('success', 'Usuario creado correctamente.');
    }

    /**
     * Pantalla de permisos de un empleado (del rol o personalizados)
     */
    public function permisos(User $usuario)
    {
        if ($usuario->hasRole('administrador')) {
            return redirect()->route('usuarios.index')->with('error', 'El administrador siempre tiene todos los permisos.');
        }

        RolPermisoController::sincronizarPermisos();

        $rol = $usuario->roles->first();

        return view('usuarios.permisos', [
            'usuario' => $usuario,
            'modulos' => RolPermisoController::MODULOS,
            'acciones' => RolPermisoController::ACCIONES,
            'permisosUsuario' => $usuario->getDirectPermissions()->pluck('name')->toArray(),
            'permisosRol' => $rol ? $rol->permissions->pluck('name')->toArray() : [],
            'nombreRol' => $rol ? ucfirst($rol->name) : 'Sin rol',
        ]);
    }

    public function actualizarPermisos(Request $request, User $usuario)
    {
        if ($usuario->hasRole('administrador')) {
            return redirect()->route('usuarios.index')->with('error', 'El administrador siempre tiene todos los permisos.');
        }

        $request->validate([
            'modo' => 'required|in:rol,personalizado',
            'permisos' => 'nullable|array',
            'permisos.*' => 'in:' . implode(',', RolPermisoController::nombresPermisos()),
        ]);

        $personalizado = $request->modo === 'personalizado';
        $permisos = $personalizado ? $request->input('permisos', []) : [];

        $usuario->update(['permisos_personalizados' => $personalizado]);
        // Sin personalizar no se guardan permisos propios: solo cuentan los del rol
        $usuario->syncPermissions($permisos);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        RegistrarBitacora::registrar('Editar', 'Usuarios', 'Permisos de ' . $usuario->name . ': ' . ($personalizado ? 'personalizados (' . (implode(', ', $permisos) ?: 'ninguno') . ')' : 'los del rol'));

        return redirect()->route('usuarios.index')->with('success', 'Permisos de ' . $usuario->name . ' actualizados correctamente.');
    }

    /**
     * Habilitar / deshabilitar un usuario
     */
    public function toggleActivo(User $usuario)
    {
        if ($usuario->id === auth()->id()) {
            return redirect()->route('usuarios.index')->with('error', 'No puedes deshabilitar tu propia cuenta.');
        }

        $usuario->update(['activo' => !$usuario->activo]);

        $accion = $usuario->activo ? 'Habilitar' : 'Deshabilitar';
        RegistrarBitacora::registrar($accion, 'Usuarios', 'Usuario ' . ($usuario->activo ? 'habilitado' : 'deshabilitado') . ': ' . $usuario->name . ' (' . $usuario->email . ')');

        return redirect()->route('usuarios.index')->with('success', 'Usuario ' . ($usuario->activo ? 'habilitado' : 'deshabilitado') . ' correctamente.');
    }

    /**
     * Eliminar definitivamente, solo si el usuario no tiene alquileres ni devoluciones registradas
     */
    public function destroy(string $id)
    {
        $usuario = User::withCount(['alquileres', 'devoluciones'])->findOrFail($id);

        if ($usuario->id === auth()->id()) {
            return redirect()->route('usuarios.index')->with('error', 'No puedes eliminar tu propia cuenta.');
        }

        if ($usuario->alquileres_count > 0 || $usuario->devoluciones_count > 0) {
            return redirect()->route('usuarios.index')->with('error', 'No se puede eliminar este usuario porque tiene alquileres o devoluciones registradas. Puede deshabilitarlo.');
        }

        try {
            $usuario->delete();
            RegistrarBitacora::registrar('Eliminar', 'Usuarios', 'Usuario eliminado: ' . $usuario->name . ' (' . $usuario->email . ')');
            return redirect()->route('usuarios.index')->with('success', 'Usuario eliminado correctamente.');
        } catch (\Exception $e) {
            return redirect()->route('usuarios.index')->with('error', 'No se puede eliminar este usuario porque tiene registros asociados. Puede deshabilitarlo.');
        }
    }
}