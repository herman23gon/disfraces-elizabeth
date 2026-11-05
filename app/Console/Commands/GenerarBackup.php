<?php

namespace App\Console\Commands;

use App\Helpers\RegistrarBitacora;
use App\Services\BackupService;
use Illuminate\Console\Command;

class GenerarBackup extends Command
{
    protected $signature = 'backup:generar {--automatico : Copia programada (respeta la configuración y limpia las copias viejas)}';

    protected $description = 'Genera una copia de seguridad de la base de datos';

    public function handle(BackupService $backups): int
    {
        $automatico = $this->option('automatico');
        $config = $backups->configuracion();

        if ($automatico && !$config['automatico_activo']) {
            $this->info('La copia automática está desactivada.');
            return self::SUCCESS;
        }

        $resultado = $backups->generar($automatico ? BackupService::PREFIJO_AUTOMATICO : BackupService::PREFIJO_MANUAL);

        if (!$resultado['ok']) {
            $this->error('Error al generar la copia: ' . $resultado['error']);
            RegistrarBitacora::registrar('Error', 'Copia de Seguridad', 'Copia automática fallida: ' . $resultado['error']);

            if ($automatico) {
                $backups->guardarConfiguracion(['ultima_automatica' => [
                    'fecha' => now()->format('d/m/Y H:i'), 'ok' => false, 'mensaje' => $resultado['error'],
                ]]);
            }

            return self::FAILURE;
        }

        $mensaje = 'Copia generada: ' . $resultado['nombre'] . '.';
        $copiaOk = true;

        if ($automatico) {
            $copia = $backups->copiarACarpetaAdicional($resultado['ruta'], $resultado['nombre']);
            $copiaOk = $copia['ok'];
            $mensaje .= ' ' . $copia['mensaje'];

            $backups->limpiar($backups->carpeta(), $config['conservar']);
            if ($copiaOk && $config['carpeta_adicional']) {
                $backups->limpiar($config['carpeta_adicional'], $config['conservar']);
            }

            $backups->guardarConfiguracion(['ultima_automatica' => [
                'fecha' => now()->format('d/m/Y H:i'), 'ok' => $copiaOk, 'mensaje' => $mensaje,
            ]]);
        }

        RegistrarBitacora::registrar('Generar', 'Copia de Seguridad', ($automatico ? 'Copia automática. ' : '') . $mensaje);
        $copiaOk ? $this->info($mensaje) : $this->warn($mensaje);

        return self::SUCCESS;
    }
}
