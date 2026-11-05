<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Http\Controllers\RolPermisoController;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('permisos_personalizados')->default(false)->after('activo');
        });

        // Crea el permiso nuevo devoluciones.crear y le da todos los permisos al administrador
        RolPermisoController::sincronizarPermisos();

        // Antes, registrar devoluciones dependía de alquileres.editar:
        // los roles que lo tenían conservan el acceso a devoluciones
        foreach (Role::all() as $rol) {
            if ($rol->hasPermissionTo('alquileres.editar')) {
                $rol->givePermissionTo('devoluciones.crear');
            }
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('permisos_personalizados');
        });
    }
};
