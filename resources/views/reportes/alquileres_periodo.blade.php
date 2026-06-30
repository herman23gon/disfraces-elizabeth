<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Reporte: Alquileres por Período
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">

                <form method="GET" class="flex gap-4 items-end mb-6">
                    <div>
                        <label class="block font-semibold mb-1">Desde</label>
                        <input type="date" name="desde" value="{{ $desde }}" class="border rounded p-2">
                    </div>
                    <div>
                        <label class="block font-semibold mb-1">Hasta</label>
                        <input type="date" name="hasta" value="{{ $hasta }}" class="border rounded p-2">
                    </div>
                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Filtrar</button>
                </form>

                <table class="w-full border text-sm mb-4">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="p-2 text-left">Recibo</th>
                            <th class="p-2 text-left">Cliente</th>
                            <th class="p-2 text-left">Fecha</th>
                            <th class="p-2 text-left">Forma de Pago</th>
                            <th class="p-2 text-left">Total</th>
                            <th class="p-2 text-left">Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($alquileres as $alquiler)
                            <tr class="border-t">
                                <td class="p-2">
                                    <a href="{{ route('alquileres.show', $alquiler->id) }}" class="text-blue-600">{{ $alquiler->numero_recibo }}</a>
                                </td>
                                <td class="p-2">{{ $alquiler->cliente->nombre }}</td>
                                <td class="p-2">{{ $alquiler->fecha_alquiler }}</td>
                                <td class="p-2">{{ $alquiler->forma_pago }}</td>
                                <td class="p-2">Bs. {{ number_format($alquiler->total, 2) }}</td>
                                <td class="p-2">{{ $alquiler->estado }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <div class="text-right text-xl font-bold bg-blue-50 p-4 rounded">
                    TOTAL DEL PERÍODO: Bs. {{ number_format($totalIngresos, 2) }}
                </div>

                <a href="{{ route('reportes.index') }}" class="text-gray-600 mt-4 inline-block">← Volver a Reportes</a>

            </div>
        </div>
    </div>
</x-app-layout>