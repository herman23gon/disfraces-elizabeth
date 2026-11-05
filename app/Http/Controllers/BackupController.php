<?php

namespace App\Http\Controllers;

use App\Helpers\RegistrarBitacora;
use App\Services\BackupService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class BackupController extends Controller
{
    public function __construct(private BackupService $backups)
    {
        $this->middleware('can:backup.ver')->only(['index']);
        // Descargar una copia expone toda la base de datos, por eso requiere el mismo permiso que generarla
        $this->middleware('can:backup.generar')->only(['generar', 'descargar']);
        // Cambiar dónde y cuándo se guardan las copias: solo administrador
        $this->middleware('role:administrador')->only(['configurar']);
    }

    public function index()
    {
        File::ensureDirectoryExists($this->backups->carpeta());

        $backups = collect(File::files($this->backups->carpeta()))
            ->filter(fn ($archivo) => $archivo->getExtension() === 'sql')
            ->map(fn ($archivo) => [
                'nombre' => $archivo->getFilename(),
                'tamano' => $archivo->getSize(),
                'fecha' => $archivo->getMTime(),
                'automatica' => str_starts_with($archivo->getFilename(), BackupService::PREFIJO_AUTOMATICO),
            ])
            ->sortByDesc('fecha')
            ->values();

        $config = $this->backups->configuracion();

        return view('backups.index', compact('backups', 'config'));
    }

    public function generar()
    {
        $resultado = $this->backups->generar();

        if (!$resultado['ok']) {
            return redirect()->route('backups.index')->with('error', 'Error al generar la copia de seguridad: ' . $resultado['error']);
        }

        RegistrarBitacora::registrar('Generar', 'Copia de Seguridad', 'Copia generada: ' . $resultado['nombre']);

        // Se vuelve a la lista (ya con la copia nueva) y la vista inicia la descarga automáticamente
        return redirect()->route('backups.index')
            ->with('success', 'Copia de seguridad generada: ' . $resultado['nombre'])
            ->with('descargar', $resultado['nombre']);
    }

    public function configurar(Request $request)
    {
        $request->validate([
            'carpeta_adicional' => 'nullable|string|max:255',
        ]);

        $carpeta = trim((string) $request->carpeta_adicional) ?: null;

        if ($carpeta && !BackupService::carpetaEscribible($carpeta)) {
            return redirect()->route('backups.index')
                ->withErrors(['carpeta_adicional' => 'La carpeta "' . $carpeta . '" no existe, no está conectada o no se puede escribir en ella.'])
                ->withInput();
        }

        $this->backups->guardarConfiguracion([
            'automatico_activo' => $request->boolean('automatico_activo'),
            'carpeta_adicional' => $carpeta,
        ]);

        RegistrarBitacora::registrar('Editar', 'Copia de Seguridad', 'Copia automática ' . ($request->boolean('automatico_activo') ? 'activada' : 'desactivada') . '. Carpeta adicional: ' . ($carpeta ?: 'ninguna'));

        return redirect()->route('backups.index')->with('success', 'Configuración de copia automática guardada.');
    }

    public function descargar(string $nombre)
    {
        $nombre = basename($nombre); // evita rutas fuera de la carpeta de backups
        $ruta = $this->backups->carpeta() . DIRECTORY_SEPARATOR . $nombre;

        abort_unless(str_ends_with($nombre, '.sql') && File::exists($ruta), 404);

        return response()->download($ruta, $nombre);
    }
}
