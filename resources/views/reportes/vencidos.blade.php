<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Reporte: Pendientes de Devolución
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">

                <div class="flex flex-wrap items-center justify-between gap-3 mb-5">
                    <div class="flex flex-wrap gap-2 text-sm">
                        <span class="px-3 py-1 rounded-full bg-red-100 text-red-800">{{ $grupos['vencidos']->count() }} vencidos</span>
                        <span class="px-3 py-1 rounded-full bg-orange-100 text-orange-800">{{ $grupos['por_vencer']->count() }} vencen hoy o mañana</span>
                        <span class="px-3 py-1 rounded-full bg-gray-100 text-gray-700">{{ $grupos['otros']->count() }} otros pendientes</span>
                    </div>
                    <a href="{{ route('reportes.vencidos', ['pdf' => 1]) }}" target="_blank"
                       class="border border-green-700 text-green-700 hover:bg-green-50 px-4 py-2 rounded-md font-semibold text-sm">🖨️ PDF</a>
                </div>

                @foreach ([
                    'vencidos' => ['Vencidos', 'text-red-700', 'No hay alquileres vencidos. 🎉'],
                    'por_vencer' => ['Vencen hoy o mañana', 'text-orange-700', 'Ninguno vence hoy ni mañana.'],
                    'otros' => ['Otros pendientes', 'text-gray-700', 'No hay otros alquileres pendientes.'],
                ] as $clave => [$titulo, $color, $vacio])
                    <h3 class="font-bold text-lg mb-2 {{ $color }}">{{ $titulo }}</h3>

                    @if ($grupos[$clave]->isEmpty())
                        <p class="text-sm text-gray-500 mb-6">{{ $vacio }}</p>
                    @else
                        <div class="overflow-x-auto mb-8">
                            <table class="w-full text-sm">
                                <thead class="bg-green-50 text-green-900">
                                    <tr>
                                        <th class="p-2 text-left">Recibo</th>
                                        <th class="p-2 text-left">Cliente</th>
                                        <th class="p-2 text-left">Trajes pendientes</th>
                                        <th class="p-2 text-left">Devolución</th>
                                        @if ($clave === 'vencidos')
                                            <th class="p-2 text-center">Atraso</th>
                                            <th class="p-2 text-right">Recargo sugerido</th>
                                        @endif
                                        <th class="p-2 text-left">Garantía retenida</th>
                                        <th class="p-2 text-right">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($grupos[$clave] as $alquiler)
                                        <tr class="border-t align-top {{ $clave === 'vencidos' ? 'bg-red-50/40' : '' }}">
                                            <td class="p-2 whitespace-nowrap">
                                                <a href="{{ route('alquileres.show', $alquiler->id) }}" class="text-green-700 hover:underline">{{ $alquiler->numero_recibo }}</a>
                                                <div class="text-xs text-gray-500">
                                                    {{ $alquiler->esGrupal() ? 'Curso' : 'Individual' }}
                                                    @if ($alquiler->estado === 'parcial')
                                                        · {{ $alquiler->detalles->count() - $alquiler->trajes_pendientes->count() }} de {{ $alquiler->detalles->count() }} devueltos
                                                    @endif
                                                </div>
                                            </td>
                                            <td class="p-2">
                                                {{ $alquiler->cliente->nombre }}
                                                <div class="text-xs text-gray-500 font-mono">{{ $alquiler->cliente->telefono }}</div>
                                            </td>
                                            <td class="p-2 text-xs">
                                                @foreach ($alquiler->trajes_pendientes as $detalle)
                                                    <div>
                                                        @if ($alquiler->esGrupal() && $detalle->persona)<strong>{{ $detalle->persona }}:</strong>@endif
                                                        {{ $detalle->traje->nombre ?? 'Traje' }}{{ $detalle->cantidad > 1 ? ' x' . $detalle->cantidad : '' }}
                                                    </div>
                                                @endforeach
                                            </td>
                                            <td class="p-2 whitespace-nowrap">{{ $alquiler->fecha_devolucion_programada_formateada }}</td>
                                            @if ($clave === 'vencidos')
                                                <td class="p-2 text-center font-semibold text-red-600 whitespace-nowrap">{{ $alquiler->dias_atraso }} día(s)</td>
                                                <td class="p-2 text-right whitespace-nowrap" title="Días de atraso × precio de los trajes pendientes">Bs. {{ number_format($alquiler->recargo_sugerido, 2) }}</td>
                                            @endif
                                            <td class="p-2 text-xs">
                                                @if ($alquiler->garantia_dinero > 0)
                                                    <div>Bs. {{ number_format($alquiler->garantia_dinero, 2) }}</div>
                                                @endif
                                                @if ($alquiler->garantia_objetos)
                                                    <div>{{ $alquiler->garantia_objetos }}</div>
                                                @endif
                                                @if (!$alquiler->garantia_dinero && !$alquiler->garantia_objetos)
                                                    <span class="text-gray-400">—</span>
                                                @endif
                                            </td>
                                            <td class="p-2 text-right whitespace-nowrap">
                                                <a href="{{ $alquiler->cliente->whatsappUrl($alquiler->cliente->mensajeRecordatorio($alquiler)) }}" target="_blank" rel="noopener"
                                                   class="inline-block bg-green-600 hover:bg-green-700 text-white px-2 py-1 rounded text-xs font-semibold">Recordar</a>
                                                @can('devoluciones.crear')
                                                    <a href="{{ route('devoluciones.create', $alquiler->id) }}"
                                                       class="inline-block bg-orange-600 hover:bg-orange-700 text-white px-2 py-1 rounded text-xs font-semibold ml-1">Devolver</a>
                                                @endcan
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                @endforeach

                <p class="text-xs text-gray-500 mb-4">Recargo sugerido: días de atraso × precio de los trajes pendientes (el recibo indica que cada día adicional se cobra). Es una sugerencia; la penalidad se decide al registrar la devolución.</p>

                <a href="{{ route('reportes.index') }}" class="text-gray-600">← Volver a Reportes</a>
            </div>
        </div>
    </div>
</x-app-layout>
