<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Reporte: Alquileres Vencidos / Pendientes
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">

                <table class="w-full border text-sm mb-4">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="p-2 text-left">Recibo</th>
                            <th class="p-2 text-left">Cliente</th>
                            <th class="p-2 text-left">Teléfono</th>
                            <th class="p-2 text-left">Fecha Alquiler</th>
                            <th class="p-2 text-left">Fecha Devolución</th>
                            <th class="p-2 text-left">Estado</th>
                            <th class="p-2 text-left">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($alquileres as $alquiler)
                            @php
                                $atrasado = \Carbon\Carbon::parse($alquiler->fecha_devolucion_programada)->isPast();
                            @endphp
                            <tr class="border-t {{ $atrasado ? 'bg-red-50' : '' }}">
                                <td class="p-2">
                                    <a href="{{ route('alquileres.show', $alquiler->id) }}" class="text-blue-600">{{ $alquiler->numero_recibo }}</a>
                                </td>
                                <td class="p-2">{{ $alquiler->cliente->nombre }}</td>
                                <td class="p-2">{{ $alquiler->cliente->telefono }}</td>
                                <td class="p-2">{{ $alquiler->fecha_alquiler }}</td>
                                <td class="p-2 {{ $atrasado ? 'text-red-600 font-bold' : '' }}">
                                    {{ $alquiler->fecha_devolucion_programada }}
                                </td>
                                <td class="p-2">
                                    @if($atrasado)
                                        <span class="bg-red-100 text-red-700 px-2 py-1 rounded text-xs">Atrasado</span>
                                    @else
                                        <span class="bg-yellow-100 text-yellow-700 px-2 py-1 rounded text-xs">Pendiente</span>
                                    @endif
                                </td>
                                <td class="p-2">
                                    <a href="{{ route('devoluciones.create', $alquiler->id) }}" class="text-orange-600">Devolver</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="p-4 text-center text-gray-500">No hay alquileres pendientes de devolución. 🎉</td></tr>
                        @endforelse
                    </tbody>
                </table>

                <a href="{{ route('reportes.index') }}" class="text-gray-600">← Volver a Reportes</a>

            </div>
        </div>
    </div>
</x-app-layout>