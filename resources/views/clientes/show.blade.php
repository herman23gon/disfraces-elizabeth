<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $cliente->nombre }}</h2>
                <p class="text-sm text-gray-500">
                    Celular: <span class="font-mono">{{ $cliente->telefono }}</span>
                    · Cliente desde {{ $cliente->created_at->format('d/m/Y') }}
                </p>
            </div>
            <div class="flex gap-2">
                <a href="{{ $cliente->whatsappUrl() }}" target="_blank" rel="noopener"
                   class="border border-green-600 text-green-700 hover:bg-green-50 px-4 py-2 rounded-lg text-sm font-semibold">
                    Abrir WhatsApp
                </a>
                @can('clientes.editar')
                <a href="{{ route('clientes.edit', $cliente->id) }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg text-sm font-semibold">
                    Editar
                </a>
                @endcan
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Resumen -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white rounded-lg shadow-sm border-l-4 border-gray-400 p-4">
                    <p class="text-xs text-gray-500">Alquileres en total</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $resumen['total'] }}</p>
                    <p class="text-xs text-gray-500">Bs. {{ number_format($resumen['total_alquilado'], 2) }} alquilados</p>
                </div>
                <div class="bg-white rounded-lg shadow-sm border-l-4 border-orange-400 p-4">
                    <p class="text-xs text-gray-500">Pendientes de devolver</p>
                    <p class="text-2xl font-bold text-orange-600">{{ $resumen['activos'] }}</p>
                </div>
                <div class="bg-white rounded-lg shadow-sm border-l-4 border-red-500 p-4">
                    <p class="text-xs text-gray-500">Vencidos</p>
                    <p class="text-2xl font-bold text-red-600">{{ $resumen['vencidos'] }}</p>
                </div>
                <div class="bg-white rounded-lg shadow-sm border-l-4 border-green-600 p-4">
                    <p class="text-xs text-gray-500">Garantía retenida</p>
                    <p class="text-2xl font-bold text-green-700">Bs. {{ number_format($resumen['garantia_retenida'], 2) }}</p>
                    @if ($resumen['documentos_retenidos']->isNotEmpty())
                        <p class="text-xs text-gray-500">+ {{ $resumen['documentos_retenidos']->implode(', ') }}</p>
                    @endif
                </div>
            </div>

            <!-- Historial -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="font-semibold text-green-900 mb-3">Historial de alquileres</h3>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-green-50 text-green-900">
                            <tr>
                                <th class="p-2 text-left">Recibo</th>
                                <th class="p-2 text-left">Trajes</th>
                                <th class="p-2 text-center">Alquiler</th>
                                <th class="p-2 text-center">Devolución</th>
                                <th class="p-2 text-right">Total</th>
                                <th class="p-2 text-left">Garantía</th>
                                <th class="p-2 text-center">Estado</th>
                                <th class="p-2 text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($cliente->alquileres as $alquiler)
                                @php
                                    $fechaDevolucion = \Carbon\Carbon::parse($alquiler->fecha_devolucion_programada);
                                    $vencido = $alquiler->estaVencido();
                                    $diasAtraso = $vencido ? $fechaDevolucion->diffInDays(today()) : 0;
                                @endphp
                                <tr class="border-t align-top hover:bg-gray-50 {{ $vencido ? 'bg-red-50/40' : '' }}">
                                    <td class="p-2 whitespace-nowrap">
                                        @can('alquileres.ver')
                                            <a href="{{ route('alquileres.show', $alquiler->id) }}" class="text-green-700 hover:underline font-mono">{{ $alquiler->numero_recibo }}</a>
                                        @else
                                            <span class="font-mono">{{ $alquiler->numero_recibo }}</span>
                                        @endcan
                                    </td>
                                    <td class="p-2">
                                        @foreach ($alquiler->detalles as $detalle)
                                            <div>{{ $detalle->cantidad }} × {{ $detalle->traje->nombre ?? 'Traje eliminado' }} {{ $detalle->traje?->talla ? '(' . strtoupper($detalle->traje->talla) . ')' : '' }}</div>
                                        @endforeach
                                    </td>
                                    <td class="p-2 text-center whitespace-nowrap">{{ $alquiler->fecha_alquiler_formateada }}</td>
                                    <td class="p-2 text-center whitespace-nowrap">
                                        @if ($alquiler->estado === 'devuelto' && $alquiler->devolucion)
                                            {{ $alquiler->devolucion->fecha_devolucion_formateada }}
                                        @else
                                            <span class="{{ $vencido ? 'text-red-600 font-semibold' : '' }}">{{ $alquiler->fecha_devolucion_programada_formateada }}</span>
                                        @endif
                                    </td>
                                    <td class="p-2 text-right whitespace-nowrap">Bs. {{ number_format($alquiler->total, 2) }}</td>
                                    <td class="p-2 text-xs">
                                        @forelse ($alquiler->garantias as $garantia)
                                            <div class="{{ $garantia->estado === 'entregada' ? 'text-gray-800' : 'text-gray-400' }}">
                                                {{ $garantia->tipo_garantia }}{{ $garantia->monto ? ': Bs. ' . number_format($garantia->monto, 2) : '' }}
                                                <span>({{ $garantia->estado === 'entregada' ? 'retenida' : 'devuelta' }})</span>
                                            </div>
                                        @empty
                                            <span class="text-gray-400">—</span>
                                        @endforelse
                                    </td>
                                    <td class="p-2 text-center whitespace-nowrap">
                                        @if ($vencido)
                                            <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-800">Vencido · {{ $diasAtraso }} día(s)</span>
                                        @elseif ($alquiler->estaPendiente())
                                            <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-orange-100 text-orange-800">Activo</span>
                                        @else
                                            <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-800">Devuelto</span>
                                        @endif
                                    </td>
                                    <td class="p-2 text-right whitespace-nowrap">
                                        @if ($alquiler->estaPendiente())
                                            <a href="{{ $cliente->whatsappUrl($cliente->mensajeRecordatorio($alquiler)) }}" target="_blank" rel="noopener"
                                               class="inline-block bg-green-600 hover:bg-green-700 text-white px-2 py-1 rounded text-xs font-semibold">
                                                Recordar
                                            </a>
                                            @can('devoluciones.crear')
                                            <a href="{{ route('devoluciones.create', $alquiler->id) }}"
                                               class="inline-block bg-orange-600 hover:bg-orange-700 text-white px-2 py-1 rounded text-xs font-semibold ml-1">
                                                Devolver
                                            </a>
                                            @endcan
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="p-6 text-center text-gray-500">Este cliente todavía no tiene alquileres.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    <a href="{{ route('clientes.index') }}" class="text-gray-600 hover:text-gray-800 text-sm">← Volver a clientes</a>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
