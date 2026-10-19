<?php

namespace App\Http\Controllers;

use App\Helpers\RegistrarBitacora;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;

class BackupController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:backup.ver')->only(['index']);
        // Descargar una copia expone toda la base de datos, por eso requiere el mismo permiso que generarla
        $this->middleware('can:backup.generar')->only(['generar', 'descargar']);
    }

    private function carpeta(): string
    {
        return storage_path('app/backups');
    }

    public function index()
    {
        File::ensureDirectoryExists($this->carpeta());

        $backups = collect(File::files($this->carpeta()))
            ->filter(fn ($archivo) => $archivo->getExtension() === 'sql')
            ->map(fn ($archivo) => [
                'nombre' => $archivo->getFilename(),
                'tamano' => $archivo->getSize(),
                'fecha' => $archivo->getMTime(),
            ])
            ->sortByDesc('fecha')
            ->values();

        return view('backups.index', compact('backups'));
    }

    public function generar()
    {
        File::ensureDirectoryExists($this->carpeta());

        $db = config('database.connections.mysql');
        $nombre = 'backup_' . now()->format('Y-m-d_H-i') . '.sql';
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
        ]);
        $process->setTimeout(300);

        try {
            $process->run();
        } catch (\Exception $e) {
            report($e);
        }

        if (!$process->isSuccessful() || !File::exists($ruta) || File::size($ruta) === 0) {
            File::delete($ruta);
            $detalle = trim($process->getErrorOutput()) ?: 'No se pudo ejecutar mysqldump. Verifique MYSQLDUMP_PATH en el archivo .env.';

            return redirect()->route('backups.index')->with('error', 'Error al generar la copia de seguridad: ' . $detalle);
        }

        RegistrarBitacora::registrar('Generar', 'Copia de Seguridad', 'Copia generada: ' . $nombre);

        return response()->download($ruta, $nombre);
    }

    public function descargar(string $nombre)
    {
        $nombre = basename($nombre); // evita rutas fuera de la carpeta de backups
        $ruta = $this->carpeta() . DIRECTORY_SEPARATOR . $nombre;

        abort_unless(str_ends_with($nombre, '.sql') && File::exists($ruta), 404);

        return response()->download($ruta, $nombre);
    }
}
