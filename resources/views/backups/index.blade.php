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
                                    <td class="p-2">{{ $backup['nombre'] }}</td>
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
