<?php

namespace App\Providers;

use App\Http\Controllers\RolPermisoController;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        //
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        Gate::before(function ($user, $ability) {
            // El administrador tiene todos los permisos y no se le pueden quitar
            if ($user->hasRole('administrador')) {
                return true;
            }

            // Solo se resuelven aquí los permisos del sistema (ej: categorias.ver)
            if (!in_array($ability, RolPermisoController::nombresPermisos())) {
                return null;
            }

            // Empleado personalizado: solo sus propias casillas
            if ($user->permisos_personalizados) {
                return $user->getDirectPermissions()->contains('name', $ability);
            }

            // Resto: los permisos de su rol
            return $user->getPermissionsViaRoles()->contains('name', $ability);
        });
    }
}
