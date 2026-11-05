<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Copia de Seguridad
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">

                @if (session('success'))
                    <div class="mb-4 p-4 bg-green-100 text-green-700 rounded">
                        {{ session('success') }}
                        @if (session('descargar'))
                            <span class="block text-sm mt-1">
                                La descarga comenzará en un momento. Si no comienza,
                                <a href="{{ route('backups.descargar', session('descargar')) }}" class="underline font-semibold">descárguela aquí</a>.
                            </span>
                            <script>
                                setTimeout(() => window.location.href = @js(route('backups.descargar', session('descargar'))), 500);
                            </script>
                        @endif
                    </div>
                @endif

                @if (session('error'))
                    <div class="mb-4 p-4 bg-red-100 text-red-700 rounded">
                        {{ session('error') }}
                    </div>
                @endif

                <p class="text-sm text-gray-600 mb-4">
                    Genera una copia completa de la base de datos en formato SQL. El archivo se guarda en el servidor
                    y se descarga automáticamente.
                </p>

                @can('backup.generar')
                <form action="{{ route('backups.generar') }}" method="POST"
                      x-data="{ generando: false }"
                      @submit="generando = true; setTimeout(() => generando = false, 8000)">
                    @csrf
                    <button type="submit" :disabled="generando"
                            class="bg-green-700 hover:bg-green-800 disabled:opacity-60 text-white px-4 py-2 rounded font-semibold">
                        <span x-show="!generando">💾 Generar y Descargar Copia de Seguridad</span>
                        <span x-show="generando" style="display: none;">Generando copia...</span>
                    </button>
                </form>
                @endcan

                <!-- Copia automática -->
                <div class="mt-8 border border-green-200 rounded-lg p-4 bg-green-50/50">
                    <h3 class="font-semibold text-green-900 mb-1">Copia automática</h3>
                    <p class="text-sm text-gray-600 mb-3">
                        Todos los días a las <strong>21:00</strong>. Si la computadora está apagada a esa hora, se hace al encenderla.
                        Se conservan las últimas {{ $config['conservar'] }} copias automáticas.
                    </p>

                    @if ($config['ultima_automatica'])
                        <div class="mb-3 text-sm p-3 rounded {{ $config['ultima_automatica']['ok'] ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                            <strong>Última copia automática ({{ $config['ultima_automatica']['fecha'] }}):</strong>
                            {{ $config['ultima_automatica']['mensaje'] }}
                        </div>
                    @else
                        <div class="mb-3 text-sm p-3 rounded bg-gray-100 text-gray-600">
                            Todavía no se ha ejecutado ninguna copia automática.
                        </div>
                    @endif

                    @role('administrador')
                    <form action="{{ route('backups.configurar') }}" method="POST" class="space-y-3">
                        @csrf
                        @method('PUT')

                        <label class="flex items-center gap-2 text-sm">
                            <input type="checkbox" name="automatico_activo" value="1" @checked(old('automatico_activo', $config['automatico_activo']))
                                   class="rounded border-gray-300 text-green-600 focus:ring-green-500">
                            Copia automática activada
                        </label>

                        <div>
                            <label for="carpeta_adicional" class="block text-sm text-gray-600 mb-1">
                                Copiar también a esta carpeta (Descargas, flash USB, etc.)
                            </label>
                            <input type="text" id="carpeta_adicional" name="carpeta_adicional"
                                   value="{{ old('carpeta_adicional', $config['carpeta_adicional']) }}"
                                   placeholder="C:\Users\...\Downloads  o  E:\Backups"
                                   class="w-full rounded-md border-gray-300 focus:border-green-600 focus:ring-green-600 text-sm font-mono">
                            @error('carpeta_adicional')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-xs text-gray-500">
                                Déjelo vacío para guardar solo en el servidor. Si la carpeta no está disponible a la hora de la copia
                                (por ejemplo, el flash desconectado), la copia igual se guarda en el servidor.
                            </p>
                        </div>

                        <button type="submit" class="bg-green-700 hover:bg-green-800 text-white px-4 py-2 rounded text-sm">
                            Guardar configuración
                        </button>
                    </form>
                    @endrole
                </div>

                <h3 class="font-semibold text-green-900 mt-8 mb-3">Copias existentes</h3>

                <div class="overflow-x-auto">
                    <table class="w-full border text-sm">
                        <thead class="bg-green-50 text-green-900">
                            <tr>
                                <th class="p-2 text-left">Nombre</th>
                                <th class="p-2 text-left">Tamaño</th>
                                <th class="p-2 text-left">Fecha</th>
                                <th class="p-2 text-left">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($backups as $backup)
                                <tr class="border-t">
                                    <td class="p-2">
                                        {{ $backup['nombre'] }}
                                        @if ($backup['automatica'])
                                            <span class="ml-1 px-2 py-0.5 rounded-full text-xs bg-green-100 text-green-800">Automática</span>
                                        @endif
                                    </td>
                                    <td class="p-2 whitespace-nowrap">
                                        @if ($backup['tamano'] >= 1048576)
                                            {{ number_format($backup['tamano'] / 1048576, 2) }} MB
                                        @else
                                            {{ number_format($backup['tamano'] / 1024, 1) }} KB
                                        @endif
                                    </td>
                                    <td class="p-2 whitespace-nowrap">{{ date('d/m/Y H:i', $backup['fecha']) }}</td>
                                    <td class="p-2">
                                        @can('backup.generar')
                                            <a href="{{ route('backups.descargar', $backup['nombre']) }}" class="text-green-700 hover:underline">Descargar</a>
                                        @else
                                            <span class="text-gray-400 text-xs">Sin permiso</span>
                                        @endcan
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="p-4 text-center text-gray-500">Aún no se ha generado ninguna copia de seguridad.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>
