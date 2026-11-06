<x-app-layout>
    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Saludo -->
            <div>
                <h1 class="text-2xl font-bold text-green-900">Bienvenido, {{ auth()->user()->name }}</h1>
                <p class="text-green-700">{{ config('negocio.nombre') }} · {{ config('negocio.eslogan') }}</p>
            </div>

            <!-- Tarjetas de estadísticas -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="bg-white rounded-lg shadow-sm border-l-4 border-green-600 p-5">
                    <p class="text-sm text-gray-500">Alquileres activos hoy</p>
                    <p class="mt-1 text-3xl font-bold text-green-700">{{ $alquileresActivos }}</p>
                </div>
                <div class="bg-white rounded-lg shadow-sm border-l-4 border-red-500 p-5">
                    <p class="text-sm text-gray-500">Alquileres vencidos</p>
                    <p class="mt-1 text-3xl font-bold text-red-600">{{ $vencidos->count() }}</p>
                </div>
                <div class="bg-white rounded-lg shadow-sm border-l-4 border-emerald-500 p-5">
                    <p class="text-sm text-gray-500">Trajes disponibles</p>
                    <p class="mt-1 text-3xl font-bold text-emerald-700">{{ $trajesDisponibles }}</p>
                </div>
            </div>

            <!-- Buscador de recibo -->
            @can('alquileres.ver')
            <div class="bg-white rounded-lg shadow-sm p-5">
                <h2 class="font-semibold text-green-900 mb-3">Buscar por número de recibo</h2>
                <form action="{{ route('dashboard.buscar-recibo') }}" method="GET" class="flex flex-col sm:flex-row gap-2">
                    <input type="text" name="recibo" value="{{ old('recibo') }}" required
                           placeholder="Ej: REC-{{ date('Y') }}-000001 o 1"
                           class="flex-1 rounded-md border-gray-300 focus:border-green-600 focus:ring-green-600">
                    <button type="submit" class="bg-green-700 hover:bg-green-800 text-white px-5 py-2 rounded-md font-semibold">
                        Buscar
                    </button>
                </form>
                @error('recibo')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            @endcan

            <!-- Alquileres vencidos -->
            <div class="bg-white rounded-lg shadow-sm p-5">
                <div class="flex items-center justify-between mb-3">
                    <h2 class="font-semibold text-green-900">Alquileres vencidos</h2>
                    @can('reportes.ver')
                        <a href="{{ route('reportes.vencidos') }}" class="text-sm text-green-700 hover:underline">Ver reporte completo</a>
                    @endcan
                </div>

                @if ($vencidos->isEmpty())
                    <p class="text-sm text-gray-500">No hay alquileres vencidos. 🎉</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm border">
                            <thead class="bg-green-50 text-green-900">
                                <tr>
                                    <th class="p-2 text-left">Recibo</th>
                                    <th class="p-2 text-left">Cliente</th>
                                    <th class="p-2 text-left">Fecha vencimiento</th>
                                    <th class="p-2 text-left">Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($vencidos as $alquiler)
                                    <tr class="border-t">
                                        <td class="p-2">
                                            @can('alquileres.ver')
                                                <a href="{{ route('alquileres.show', $alquiler->id) }}" class="text-green-700 hover:underline">{{ $alquiler->numero_recibo }}</a>
                                            @else
                                                {{ $alquiler->numero_recibo }}
                                            @endcan
                                        </td>
                                        <td class="p-2">{{ $alquiler->cliente->nombre }}</td>
                                        <td class="p-2 text-red-600 font-semibold">{{ $alquiler->fecha_devolucion_programada_formateada }}</td>
                                        <td class="p-2">
                                            @can('devoluciones.crear')
                                                <a href="{{ route('devoluciones.create', $alquiler->id) }}" class="inline-block bg-orange-600 hover:bg-orange-700 text-white px-3 py-1 rounded text-xs font-semibold">
                                                    Devolver
                                                </a>
                                            @endcan
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
