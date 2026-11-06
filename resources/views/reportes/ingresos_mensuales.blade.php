<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Reporte: Ingresos Mensuales
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">

                <form method="GET" class="flex flex-wrap gap-3 items-end mb-6">
                    <div>
                        <label class="block font-semibold mb-1">Mes</label>
                        <input type="month" name="mes" value="{{ $mes }}" class="border-gray-300 rounded-md">
                    </div>
                    <button type="submit" class="bg-green-700 hover:bg-green-800 text-white px-4 py-2 rounded-md">Consultar</button>
                    <a href="{{ route('reportes.ingresos-mensuales', ['mes' => $mes, 'pdf' => 1]) }}" target="_blank"
                       class="border border-green-700 text-green-700 hover:bg-green-50 px-4 py-2 rounded-md font-semibold">🖨️ PDF</a>
                </form>

                <h3 class="font-bold text-lg mb-3">{{ $nombreMes }} · Ingresos (alquileres + penalidades)</h3>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-2">
                    <div class="bg-green-50 p-4 rounded text-center">
                        <div class="text-sm text-gray-600">Efectivo</div>
                        <div class="text-xl font-bold text-green-700">Bs. {{ number_format($ingresos['efectivo'], 2) }}</div>
                    </div>
                    <div class="bg-blue-50 p-4 rounded text-center">
                        <div class="text-sm text-gray-600">Pago QR</div>
                        <div class="text-xl font-bold text-blue-700">Bs. {{ number_format($ingresos['qr'], 2) }}</div>
                    </div>
                    <div class="bg-gray-100 p-4 rounded text-center">
                        <div class="text-sm text-gray-600">Total ({{ $ingresos['cantidad'] }} alquileres)</div>
                        <div class="text-xl font-bold">Bs. {{ number_format($ingresos['total'], 2) }}</div>
                        @if ($variacion !== null)
                            <div class="text-xs mt-1 {{ $variacion >= 0 ? 'text-green-700' : 'text-red-600' }}">
                                {{ $variacion >= 0 ? '▲' : '▼' }} {{ abs($variacion) }}% vs {{ $nombreMesAnterior }} (Bs. {{ number_format($totalAnterior, 2) }})
                            </div>
                        @endif
                    </div>
                </div>
                <div class="text-sm text-gray-500 mb-6 space-x-3">
                    <span>Penalidades cobradas: Bs. {{ number_format($ingresos['penalidad_efectivo'] + $ingresos['penalidad_qr'], 2) }}</span>
                    <span>· Descuentos dados (precio grupal): Bs. {{ number_format($descuentos, 2) }}</span>
                </div>

                <!-- Garantías en dinero -->
                <div class="mb-6 p-4 rounded-lg border border-yellow-200 bg-yellow-50">
                    <h3 class="font-bold mb-1">Garantías en dinero</h3>
                    <p class="text-xs text-gray-600 mb-3">No son ingreso (se devuelven al cliente), pero están en la caja: tenerlas en cuenta al cerrar caja.</p>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-sm">
                        <div>Recibidas en el mes: <strong>Bs. {{ number_format($garantias['recibidas_efectivo'], 2) }}</strong> efectivo · Bs. {{ number_format($garantias['recibidas_qr'], 2) }} QR</div>
                        <div>Devueltas en el mes: <strong>Bs. {{ number_format($garantias['devueltas_efectivo'], 2) }}</strong> efectivo · Bs. {{ number_format($garantias['devueltas_qr'], 2) }} QR</div>
                        <div class="font-semibold text-yellow-900">En custodia hoy: Bs. {{ number_format($garantias['en_custodia'], 2) }}</div>
                    </div>
                </div>

                <h3 class="font-bold text-lg mb-3">Desglose diario (control de caja)</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm mb-2">
                        <thead class="bg-green-50 text-green-900">
                            <tr>
                                <th class="p-2 text-left">Fecha</th>
                                <th class="p-2 text-center">Alquileres</th>
                                <th class="p-2 text-right">Efectivo</th>
                                <th class="p-2 text-right">QR</th>
                                <th class="p-2 text-right">Total ingresos</th>
                                <th class="p-2 text-right">Garantía recibida</th>
                                <th class="p-2 text-right">Garantía devuelta</th>
                                <th class="p-2 text-right">Efectivo neto del día</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($dias as $fecha => $d)
                                <tr class="border-t">
                                    <td class="p-2">{{ \Carbon\Carbon::parse($fecha)->format('d/m/Y') }}</td>
                                    <td class="p-2 text-center">{{ $d['cantidad'] }}</td>
                                    <td class="p-2 text-right">{{ number_format($d['efectivo'], 2) }}</td>
                                    <td class="p-2 text-right">{{ number_format($d['qr'], 2) }}</td>
                                    <td class="p-2 text-right font-semibold">{{ number_format($d['efectivo'] + $d['qr'], 2) }}</td>
                                    <td class="p-2 text-right text-green-700">{{ $d['garantia_entra'] ? '+' . number_format($d['garantia_entra'], 2) : '—' }}</td>
                                    <td class="p-2 text-right text-red-600">{{ $d['garantia_sale'] ? '-' . number_format($d['garantia_sale'], 2) : '—' }}</td>
                                    <td class="p-2 text-right font-semibold">{{ number_format($d['efectivo'] + $d['garantia_entra'] - $d['garantia_sale'], 2) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="8" class="p-4 text-center text-gray-500">No hay movimientos registrados en este mes.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <p class="text-xs text-gray-500 mb-4">Efectivo neto del día = ingresos en efectivo + garantías en efectivo recibidas − garantías en efectivo devueltas. Es lo que debería sumar el cajón ese día.</p>

                <a href="{{ route('reportes.index') }}" class="text-gray-600">← Volver a Reportes</a>
            </div>
        </div>
    </div>
</x-app-layout>
