<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Alquiler {{ $alquiler->numero_recibo }}
            <span class="ml-2 text-sm font-normal px-2 py-0.5 rounded-full {{ $alquiler->esGrupal() ? 'bg-purple-100 text-purple-800' : 'bg-gray-100 text-gray-700' }}">
                {{ $alquiler->esGrupal() ? 'Curso / grupo' : 'Individual' }}
            </span>
        </h2>
    </x-slot>

    @php
        $devueltos = $alquiler->detalles->whereNotNull('devolucion_id')->count();
        $totalTrajes = $alquiler->detalles->count();
        $garantiaRetenida = $alquiler->garantias->where('estado', 'entregada')->sum('monto');
    @endphp

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
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
                @if (session('trajes_reparacion'))
                    <div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-800 rounded">
                        <p class="font-semibold mb-1">Enviados a reparación (no se alquilan hasta repararlos):</p>
                        <ul class="text-sm space-y-1">
                            @foreach (session('trajes_reparacion') as $traje)
                                <li>
                                    {{ $traje['nombre'] }}{{ $traje['persona'] ? ' (' . $traje['persona'] . ')' : '' }}
                                    @can('trajes.editar')
                                        — <a href="{{ route('trajes.edit', $traje['id']) }}" class="underline font-semibold">ver en Trajes</a>
                                    @endcan
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="flex flex-wrap justify-between gap-4 mb-6">
                    <table class="text-sm">
                        <tr><td class="font-semibold p-1 pr-4">{{ $alquiler->esGrupal() ? 'Responsable:' : 'Cliente (Responsable):' }}</td><td>{{ $alquiler->cliente->nombre }} - {{ $alquiler->cliente->telefono }}</td></tr>
                        @if (!$alquiler->esGrupal() && $alquiler->nombre_usuario_traje)
                            <tr><td class="font-semibold p-1 pr-4">Usa el traje:</td><td>{{ $alquiler->nombre_usuario_traje }}</td></tr>
                        @endif
                        <tr><td class="font-semibold p-1 pr-4">Colegio/Dirección:</td><td>{{ $alquiler->colegio_direccion ?: '—' }}</td></tr>
                        <tr><td class="font-semibold p-1 pr-4">Curso / Turno:</td><td>{{ $alquiler->curso ?: '—' }} / {{ $alquiler->turno ?: '—' }}</td></tr>
                        <tr><td class="font-semibold p-1 pr-4">Fecha alquiler:</td><td>{{ $alquiler->fecha_alquiler_formateada }}</td></tr>
                        <tr><td class="font-semibold p-1 pr-4">Devolución programada:</td><td class="{{ $alquiler->estaVencido() ? 'text-red-600 font-semibold' : '' }}">{{ $alquiler->fecha_devolucion_programada_formateada }}</td></tr>
                        <tr><td class="font-semibold p-1 pr-4">Forma de pago:</td><td>{{ $alquiler->forma_pago }}</td></tr>
                    </table>

                    <div class="flex flex-col items-end gap-2">
                        @if ($alquiler->estado === 'devuelto')
                            <span class="px-3 py-1 rounded-full text-sm font-semibold bg-green-100 text-green-800">Devuelto</span>
                        @elseif ($alquiler->estaVencido())
                            <span class="px-3 py-1 rounded-full text-sm font-semibold bg-red-100 text-red-800">Vencido · {{ $devueltos }} de {{ $totalTrajes }} devueltos</span>
                        @elseif ($alquiler->estado === 'parcial')
                            <span class="px-3 py-1 rounded-full text-sm font-semibold bg-orange-100 text-orange-800">Parcial · {{ $devueltos }} de {{ $totalTrajes }} devueltos</span>
                        @else
                            <span class="px-3 py-1 rounded-full text-sm font-semibold bg-yellow-100 text-yellow-800">Activo</span>
                        @endif
                    </div>
                </div>

                <h3 class="font-bold text-lg mb-3">{{ $alquiler->esGrupal() ? 'Trajes por persona' : 'Trajes' }}</h3>
                <div class="overflow-x-auto mb-6">
                    <table class="w-full border text-sm">
                        <thead class="bg-gray-100">
                            <tr>
                                @if ($alquiler->esGrupal())
                                    <th class="p-2 text-left">Persona</th>
                                @endif
                                <th class="p-2 text-left">Traje</th>
                                <th class="p-2 text-left">Piezas</th>
                                <th class="p-2 text-right">Precio</th>
                                <th class="p-2 text-right">Subtotal</th>
                                @if ($alquiler->esGrupal())
                                    <th class="p-2 text-left">Garantía</th>
                                @endif
                                <th class="p-2 text-left">Devolución</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($alquiler->detalles as $detalle)
                                <tr class="border-t align-top">
                                    @if ($alquiler->esGrupal())
                                        <td class="p-2 font-semibold">{{ $detalle->persona ?: '—' }}</td>
                                    @endif
                                    <td class="p-2">
                                        {{ $detalle->cantidad }} × {{ $detalle->traje->nombre ?? 'Traje eliminado' }}
                                        @if ($detalle->traje?->talla)
                                            <span class="text-gray-500">({{ strtoupper($detalle->traje->talla) }})</span>
                                        @endif
                                        @if ($detalle->estado_entrega)
                                            <div class="text-xs text-gray-500">Obs.: {{ $detalle->estado_entrega }}</div>
                                        @endif
                                    </td>
                                    <td class="p-2 text-xs">
                                        {{ $detalle->piezas_entregadas ? str_replace(',', ', ', $detalle->piezas_entregadas) : $detalle->cantidad_piezas . ' pieza(s)' }}
                                        @if ($detalle->estado_salida)
                                            <div class="text-gray-500 mt-1">Salió: {{ $detalle->estado_salida }}</div>
                                        @endif
                                    </td>
                                    <td class="p-2 text-right whitespace-nowrap">Bs. {{ number_format($detalle->precio_unitario, 2) }}</td>
                                    <td class="p-2 text-right whitespace-nowrap">Bs. {{ number_format($detalle->subtotal, 2) }}</td>
                                    @if ($alquiler->esGrupal())
                                        <td class="p-2 text-xs">
                                            @forelse ($detalle->garantias as $garantia)
                                                <div class="{{ $garantia->estado === 'devuelta' ? 'text-gray-400 line-through' : '' }}">{{ $garantia->resumen }}</div>
                                            @empty
                                                <span class="text-gray-400">Del grupo</span>
                                            @endforelse
                                        </td>
                                    @endif
                                    <td class="p-2 text-xs whitespace-nowrap">
                                        @if ($detalle->estaDevuelto())
                                            <span class="px-2 py-0.5 rounded-full bg-green-100 text-green-800">Devuelto {{ $detalle->devolucion?->fecha_devolucion_formateada }}</span>
                                            @if ($detalle->estado_retorno)
                                                <div class="mt-1 {{ $detalle->volvioPeor() ? 'text-red-600 font-semibold' : 'text-gray-600' }}">
                                                    Volvió: {{ $detalle->estado_retorno }}
                                                </div>
                                            @endif
                                            @if ($detalle->reparacion)
                                                <div class="mt-1 text-red-700">
                                                    → {{ ['en_reparacion' => 'En reparación', 'reparado' => 'Reparado', 'baja' => 'Dado de baja'][$detalle->reparacion->estado] }}
                                                </div>
                                            @endif
                                            @if ($detalle->piezas_faltantes)
                                                <div class="text-red-600 mt-1">Faltó: {{ str_replace(',', ', ', $detalle->piezas_faltantes) }}</div>
                                            @endif
                                            @if ($detalle->penalidad > 0)
                                                <div class="text-red-600">Penalidad Bs. {{ number_format($detalle->penalidad, 2) }}</div>
                                            @endif
                                        @else
                                            <span class="px-2 py-0.5 rounded-full {{ $alquiler->estaVencido() ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800' }}">Pendiente</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <h3 class="font-bold text-lg mb-3">{{ $alquiler->esGrupal() ? 'Garantías del grupo' : 'Garantías' }}</h3>
                @if ($alquiler->garantiasGenerales->isEmpty())
                    <p class="text-sm text-gray-500 mb-6">{{ $alquiler->esGrupal() ? 'No se dejó garantía del grupo.' : 'No se registraron garantías.' }}</p>
                @else
                    <table class="w-full mb-6 border text-sm">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="p-2 text-left">Tipo</th>
                                <th class="p-2 text-left">Cantidad</th>
                                <th class="p-2 text-left">Monto</th>
                                <th class="p-2 text-left">Descripción</th>
                                <th class="p-2 text-left">Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($alquiler->garantiasGenerales as $garantia)
                                <tr class="border-t">
                                    <td class="p-2">{{ $garantia->tipo_garantia }}</td>
                                    <td class="p-2">{{ $garantia->cantidad }}</td>
                                    <td class="p-2">{{ $garantia->monto ? 'Bs. ' . number_format($garantia->monto, 2) : '-' }}</td>
                                    <td class="p-2">{{ $garantia->descripcion }}</td>
                                    <td class="p-2">{{ $garantia->estado === 'entregada' ? 'Retenida' : 'Devuelta' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif

                <div class="flex flex-wrap justify-end gap-6 mb-6 mt-4">
                    <div class="text-center bg-blue-50 rounded p-4" style="min-width: 200px;">
                        <div class="text-sm text-gray-600 mb-1">TOTAL A PAGAR (Alquiler)</div>
                        <div class="text-2xl font-bold text-blue-700">Bs. {{ number_format($alquiler->total, 2) }}</div>
                    </div>
                    <div class="text-center bg-green-50 rounded p-4" style="min-width: 200px;">
                        <div class="text-sm text-gray-600 mb-1">GARANTÍA RETENIDA</div>
                        <div class="text-sm text-gray-500 mb-1">(a devolver)</div>
                        <div class="text-2xl font-bold text-green-700">Bs. {{ number_format($garantiaRetenida, 2) }}</div>
                    </div>
                </div>

                <a href="{{ route('alquileres.recibo', $alquiler->id) }}" target="_blank" class="bg-green-600 text-white px-4 py-2 rounded inline-block mb-4">
                    🖨️ Imprimir Recibo
                </a>
                @if ($alquiler->estaPendiente())
                    @can('devoluciones.crear')
                    <a href="{{ route('devoluciones.create', $alquiler->id) }}" class="bg-orange-600 text-white px-4 py-2 rounded inline-block mb-4 ml-2">
                        📦 {{ $alquiler->estado === 'parcial' ? 'Registrar otra devolución' : 'Registrar Devolución' }}
                    </a>
                    @endcan
                    <a href="{{ $alquiler->cliente->whatsappUrl($alquiler->cliente->mensajeRecordatorio($alquiler)) }}" target="_blank" rel="noopener"
                       class="bg-green-700 text-white px-4 py-2 rounded inline-block mb-4 ml-2">
                        Recordar por WhatsApp
                    </a>
                @endif

                <!-- Historial de devoluciones -->
                @if ($alquiler->devoluciones->isNotEmpty())
                    <div class="border-t pt-4 mt-4">
                        <h3 class="font-bold text-lg mb-3 text-green-700">
                            {{ $alquiler->devoluciones->count() > 1 ? 'Devoluciones registradas' : 'Devolución registrada' }}
                        </h3>
                        @foreach ($alquiler->devoluciones->sortBy('fecha_devolucion') as $devolucion)
                            <table class="w-full mb-4 text-sm border">
                                <tr><td class="font-semibold p-2 w-48">Fecha:</td><td class="p-2">{{ $devolucion->fecha_devolucion_formateada }}</td></tr>
                                <tr><td class="font-semibold p-2">Recibido por:</td><td class="p-2">{{ $devolucion->usuario->name ?? '—' }}</td></tr>
                                <tr>
                                    <td class="font-semibold p-2">Trajes devueltos:</td>
                                    <td class="p-2">
                                        {{ $devolucion->detalles->map(fn ($d) => ($alquiler->esGrupal() && $d->persona ? $d->persona . ': ' : '') . ($d->traje->nombre ?? 'Traje'))->implode(' · ') ?: '—' }}
                                    </td>
                                </tr>
                                <tr><td class="font-semibold p-2">Observaciones:</td><td class="p-2">{{ $devolucion->observaciones ?? '-' }}</td></tr>
                                @if ($devolucion->penalidad > 0)
                                    <tr><td class="font-semibold p-2">Penalidad cobrada:</td><td class="p-2">Bs. {{ number_format($devolucion->penalidad, 2) }} ({{ $devolucion->forma_pago_penalidad }})</td></tr>
                                @endif
                            </table>
                        @endforeach
                    </div>
                @endif

                <a href="{{ route('alquileres.index') }}" class="text-gray-600">← Volver a la lista</a>
            </div>
        </div>
    </div>
</x-app-layout>
