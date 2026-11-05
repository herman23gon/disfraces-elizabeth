<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;

class BackupService
{
    public const PREFIJO_MANUAL = 'backup_';
    public const PREFIJO_AUTOMATICO = 'backup_auto_';

    /**
     * Carpeta principal donde se guardan todas las copias
     */
    public function carpeta(): string
    {
        return storage_path('app/backups');
    }

    /**
     * Ejecuta mysqldump y guarda la copia en la carpeta principal.
     * Devuelve ['ok' => bool, 'nombre' => string, 'ruta' => string, 'error' => ?string]
     */
    public function generar(string $prefijo = self::PREFIJO_MANUAL): array
    {
        File::ensureDirectoryExists($this->carpeta());

        $db = config('database.connections.mysql');
        $nombre = $prefijo . now()->format('Y-m-d_H-i') . '.sql';
        $ruta = $this->carpeta() . DIRECTORY_SEPARATOR . $nombre;

        // Equivale a: mysqldump --user=root --password= --host=127.0.0.1 disfraces_elizabeth > backup.sql
        $process = new Process([
            $db['dump_path'] ?? 'mysqldump',
            '--user=' . $db['username'],
            '--host=' . $db['host'],
            '--port=' . $db['port'],
            '--routines',
            '--result-file=' . $ruta,
            $db['database'],
        ], null, [
            // La contraseña va por variable de entorno para no exponerla en la línea de comandos
            'MYSQL_PWD' => (string) $db['password'],
            // En Windows, desde el servidor web (Apache) estas variables no llegan al proceso
            // y sin SYSTEMROOT mysqldump no puede abrir la conexión (error 2004 / 10106)
            'SYSTEMROOT' => getenv('SYSTEMROOT') ?: 'C:\\Windows',
            'SYSTEMDRIVE' => getenv('SYSTEMDRIVE') ?: 'C:',
            'TEMP' => getenv('TEMP') ?: sys_get_temp_dir(),
        ]);
        $process->setTimeout(300);

        try {
            $process->run();
        } catch (\Exception $e) {
            report($e);
        }

        if (!$process->isSuccessful() || !File::exists($ruta) || File::size($ruta) === 0) {
            File::delete($ruta);

            return [
                'ok' => false,
                'nombre' => $nombre,
                'ruta' => $ruta,
                'error' => trim($process->getErrorOutput()) ?: 'No se pudo ejecutar mysqldump. Verifique MYSQLDUMP_PATH en el archivo .env.',
            ];
        }

        return ['ok' => true, 'nombre' => $nombre, 'ruta' => $ruta, 'error' => null];
    }

    /**
     * Copia un archivo a la carpeta adicional (Descargas, flash, etc.)
     */
    public function copiarACarpetaAdicional(string $ruta, string $nombre): array
    {
        $carpeta = $this->configuracion()['carpeta_adicional'];

        if (!$carpeta) {
            return ['ok' => true, 'mensaje' => 'Sin carpeta adicional configurada.'];
        }

        if (!self::carpetaEscribible($carpeta)) {
            return ['ok' => false, 'mensaje' => 'No se pudo copiar a ' . $carpeta . ': la carpeta no existe o no está conectada.'];
        }

        if (!@copy($ruta, rtrim($carpeta, '\\/') . DIRECTORY_SEPARATOR . $nombre)) {
            return ['ok' => false, 'mensaje' => 'No se pudo copiar a ' . $carpeta . '.'];
        }

        return ['ok' => true, 'mensaje' => 'Copiada también a ' . $carpeta . '.'];
    }

    /**
     * Verifica que la carpeta exista y se pueda escribir en ella.
     * Se prueba escribiendo un archivo porque is_writable() da falsos negativos en Windows
     * (por ejemplo en Descargas).
     */
    public static function carpetaEscribible(string $carpeta): bool
    {
        if (!is_dir($carpeta)) {
            return false;
        }

        $prueba = rtrim($carpeta, '\\/') . DIRECTORY_SEPARATOR . '.prueba_backup_' . uniqid() . '.tmp';

        if (@file_put_contents($prueba, '') === false) {
            return false;
        }

        @unlink($prueba);

        return true;
    }

    /**
     * Borra las copias automáticas más antiguas de una carpeta, conservando las últimas $conservar.
     * Solo toca archivos backup_auto_*.sql creados por el sistema.
     */
    public function limpiar(string $carpeta, int $conservar): int
    {
        if (!is_dir($carpeta)) {
            return 0;
        }

        $viejas = collect(File::files($carpeta))
            ->filter(fn ($archivo) => preg_match('/^backup_auto_\d{4}-\d{2}-\d{2}_\d{2}-\d{2}\.sql$/', $archivo->getFilename()))
            ->sortByDesc(fn ($archivo) => $archivo->getFilename()) // el nombre lleva la fecha, ordena cronológicamente
            ->slice($conservar);

        foreach ($viejas as $archivo) {
            File::delete($archivo->getPathname());
        }

        return $viejas->count();
    }

    /**
     * Configuración de la copia automática, guardada en storage/app/backup-config.json
     */
    public function configuracion(): array
    {
        $guardada = [];
        $archivo = storage_path('app/backup-config.json');

        if (File::exists($archivo)) {
            $guardada = json_decode(File::get($archivo), true) ?: [];
        }

        return array_merge([
            'automatico_activo' => true,
            'carpeta_adicional' => config('backup.carpeta_adicional'),
            'conservar' => config('backup.conservar'),
            'ultima_automatica' => null,
        ], $guardada);
    }

    public function guardarConfiguracion(array $datos): void
    {
        $config = array_merge($this->configuracion(), $datos);
        File::put(storage_path('app/backup-config.json'), json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }
}
