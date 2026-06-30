<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Reporte: Ingresos Mensuales
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">

                <form method="GET" class="flex gap-4 items-end mb-6">
                    <div>
                        <label class="block font-semibold mb-1">Mes</label>
                        <input type="month" name="mes" value="{{ $mes }}" class="border rounded p-2">
                    </div>
                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Consultar</button>
                </form>

                <h3 class="font-bold text-lg mb-3">Resumen del Mes (Alquileres + Penalidades)</h3>
                <div class="grid grid-cols-3 gap-4 mb-2">
                    <div class="bg-green-50 p-4 rounded text-center">
                        <div class="text-sm text-gray-600">Total Efectivo</div>
                        <div class="text-xl font-bold text-green-700">Bs. {{ number_format($totalEfectivo, 2) }}</div>
                    </div>
                    <div class="bg-blue-50 p-4 rounded text-center">
                        <div class="text-sm text-gray-600">Total Pago QR</div>
                        <div class="text-xl font-bold text-blue-700">Bs. {{ number_format($totalQR, 2) }}</div>
                    </div>
                    <div class="bg-gray-100 p-4 rounded text-center">
                        <div class="text-sm text-gray-600">Total General ({{ $cantidadAlquileres }} alquileres)</div>
                        <div class="text-xl font-bold">Bs. {{ number_format($totalGeneral, 2) }}</div>
                    </div>
                </div>

                @if ($totalEfectivoPenalidad > 0 || $totalQRPenalidad > 0)
                <div class="text-sm text-gray-500 mb-6">
                    Incluye penalidades cobradas: Bs. {{ number_format($totalEfectivoPenalidad, 2) }} en efectivo, Bs. {{ number_format($totalQRPenalidad, 2) }} en QR.
                </div>
                @else
                <div class="mb-6"></div>
                @endif

                <h3 class="font-bold text-lg mb-3">Desglose Diario (control de caja: alquileres + penalidades)</h3>
                <table class="w-full border text-sm mb-4">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="p-2 text-left">Fecha</th>
                            <th class="p-2 text-left">Cant. Alquileres</th>
                            <th class="p-2 text-left">Efectivo</th>
                            <th class="p-2 text-left">QR</th>
                            <th class="p-2 text-left">Total Día</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($desgloseDiario as $fecha => $datos)
                            <tr class="border-t">
                                <td class="p-2">{{ \Carbon\Carbon::parse($fecha)->format('d/m/Y') }}</td>
                                <td class="p-2">{{ $datos['cantidad'] }}</td>
                                <td class="p-2">Bs. {{ number_format($datos['efectivo'], 2) }}</td>
                                <td class="p-2">Bs. {{ number_format($datos['qr'], 2) }}</td>
                                <td class="p-2 font-semibold">Bs. {{ number_format($datos['total'], 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="p-4 text-center text-gray-500">No hay movimientos registrados en este mes.</td></tr>
                        @endforelse
                    </tbody>
                </table>

                <a href="{{ route('reportes.index') }}" class="text-gray-600">← Volver a Reportes</a>

            </div>
        </div>
    </div>
</x-app-layout>