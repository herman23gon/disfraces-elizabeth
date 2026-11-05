<?php

namespace App\Helpers;

use App\Models\Bitacora;

class RegistrarBitacora
{
    /**
     * Guarda una acción en la bitácora con el usuario autenticado y su IP
     */
    public static function registrar(string $accion, string $modulo, ?string $descripcion = null): void
    {
        try {
            Bitacora::create([
                'user_id' => auth()->id(),
                'accion' => $accion,
                'modulo' => $modulo,
                'descripcion' => $descripcion,
                // Las tareas automáticas (consola) no tienen usuario ni IP: se muestran como "Sistema"
                'ip' => app()->runningInConsole() ? null : request()->ip(),
            ]);
        } catch (\Exception $e) {
            // Un fallo en la bitácora no debe interrumpir la operación principal
            report($e);
        }
    }
}
