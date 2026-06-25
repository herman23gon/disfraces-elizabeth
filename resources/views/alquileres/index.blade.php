<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Alquileres
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">

                @if (session('success'))
                    <div class="mb-4 p-4 bg-green-100 text-green-700 rounded">
                        {{ session('success') }}
                    </div>
                @endif

                <a href="{{ route('alquileres.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded mb-4 inline-block">
                    + Nuevo Alquiler
                </a>

                <table class="w-full mt-4 border text-sm">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="p-2 text-left">Recibo</th>
                            <th class="p-2 text-left">Cliente</th>
                            <th class="p-2 text-left">Fecha Alquiler</th>
                            <th class="p-2 text-left">Fecha Devolución</th>
                            <th class="p-2 text-left">Total</th>
                            <th class="p-2 text-left">Estado</th>
                            <th class="p-2 text-left">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($alquileres as $alquiler)
                            <tr class="border-t">
                                <td class="p-2">{{ $alquiler->numero_recibo }}</td>
                                <td class="p-2">{{ $alquiler->cliente->nombre }}</td>
                                <td class="p-2">{{ $alquiler->fecha_alquiler }}</td>
                                <td class="p-2">{{ $alquiler->fecha_devolucion_programada }}</td>
                                <td class="p-2">Bs. {{ number_format($alquiler->total, 2) }}</td>
                                <td class="p-2">
                                    @if($alquiler->estado == 'activo')
                                        <span class="bg-yellow-100 text-yellow-700 px-2 py-1 rounded text-xs">Activo</span>
                                    @else
                                        <span class="bg-green-100 text-green-700 px-2 py-1 rounded text-xs">Devuelto</span>
                                    @endif
                                </td>
                                <td class="p-2">
                                    <a href="{{ route('alquileres.show', $alquiler->id) }}" class="text-blue-600">Ver</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

            </div>
        </div>
    </div>
</x-app-layout>