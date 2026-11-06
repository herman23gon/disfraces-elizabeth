<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Registrar Devolución - {{ $alquiler->numero_recibo }}
        </h2>
    </x-slot>

    @php
        $pendientes = $alquiler->detalles->whereNull('devolucion_id');
        $devueltos = $alquiler->detalles->whereNotNull('devolucion_id');
        // En individual se marcan todos por defecto; en un curso se marca solo quien devuelve hoy
        $marcarTodos = !$alquiler->esGrupal();
    @endphp

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6"
                 x-data="{
                     marcados: @js(old('devolver', $marcarTodos ? $pendientes->pluck('id')->map(fn ($id) => (string) $id)->values() : [])),
                     penalidades: @js((object) old('penalidad', [])),
                     estados: @js((object) old('estado_retorno', $pendientes->mapWithKeys(fn ($d) => [$d->id => 'Sano'])->all())),
                     reparacion: @js((object) collect(old('reparacion', []))->map(fn () => true)->all()),
                     nivelSalida: @js($pendientes->mapWithKeys(fn ($d) => [$d->id => \App\Models\DetalleAlquiler::NIVEL_ESTADO[$d->estado_salida] ?? 0])->all()),
                     niveles: @js(\App\Models\DetalleAlquiler::NIVEL_ESTADO),
                     totalPendientes: {{ $pendientes->count() }},
                     volvioPeor(id) {
                         return (this.niveles[this.estados[id]] ?? 0) > (this.nivelSalida[id] ?? 0);
                     },
                     totalPenalidad() {
                         return this.marcados.reduce((s, id) => s + Number(this.penalidades[id] || 0), 0);
                     },
                 }">

                @if ($errors->any())
                    <div class="mb-4 p-4 bg-red-100 text-red-700 rounded">
                        @foreach (collect($errors->all())->unique() as $error)
                            <p>{{ $error }}</p>
                        @endforeach
                    </div>
                @endif

                <!-- Resumen -->
                <div class="flex flex-wrap justify-between gap-3 mb-5 text-sm">
                    <div>
                        <p><span class="font-semibold">{{ $alquiler->esGrupal() ? 'Responsable' : 'Cliente' }}:</span> {{ $alquiler->cliente->nombre }} · {{ $alquiler->cliente->telefono }}</p>
                        @if ($alquiler->colegio_direccion || $alquiler->curso)
                            <p><span class="font-semibold">Colegio / curso:</span> {{ $alquiler->colegio_direccion }} {{ $alquiler->curso }} {{ $alquiler->turno }}</p>
                        @endif
                        <p>
                            <span class="font-semibold">Alquilado:</span> {{ $alquiler->fecha_alquiler_formateada }} ·
                            <span class="font-semibold">Devolución programada:</span>
                            <span class="{{ $alquiler->estaVencido() ? 'text-red-600 font-semibold' : '' }}">{{ $alquiler->fecha_devolucion_programada_formateada }}</span>
                        </p>
                    </div>
                    <div class="text-right">
                        <span class="px-3 py-1 rounded-full text-sm font-semibold {{ $devueltos->isEmpty() ? 'bg-yellow-100 text-yellow-800' : 'bg-orange-100 text-orange-800' }}">
                            {{ $devueltos->count() }} de {{ $alquiler->detalles->count() }} trajes devueltos
                        </span>
                    </div>
                </div>

                <form action="{{ route('devoluciones.store', $alquiler->id) }}" method="POST">
                    @csrf

                    <div class="flex flex-wrap items-center justify-between gap-2 mb-2">
                        <h3 class="font-bold text-lg">Trajes pendientes</h3>
                        <div class="text-sm space-x-3">
                            <button type="button" @click="marcados = @js($pendientes->pluck('id')->map(fn ($id) => (string) $id)->values())" class="text-green-700 hover:underline">Marcar todos</button>
                            <button type="button" @click="marcados = []" class="text-gray-600 hover:underline">Desmarcar</button>
                        </div>
                    </div>

                    <div class="overflow-x-auto mb-6">
                        <table class="w-full text-sm">
                            <thead class="bg-green-50 text-green-900">
                                <tr>
                                    <th class="p-2 text-center w-20">Devuelve hoy</th>
                                    @if ($alquiler->esGrupal())
                                        <th class="p-2 text-left">Persona</th>
                                    @endif
                                    <th class="p-2 text-left">Traje</th>
                                    <th class="p-2 text-left">Piezas que vuelven</th>
                                    <th class="p-2 text-left w-40">Estado</th>
                                    <th class="p-2 text-left">Garantía a entregarle</th>
                                    <th class="p-2 text-left w-28">Penalidad (Bs.)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($pendientes as $detalle)
                                    @php
                                        $id = (string) $detalle->id;
                                        $piezasViejas = old("piezas.$id");
                                    @endphp
                                    <tr class="border-t align-top" :class="marcados.includes('{{ $id }}') ? 'bg-yellow-50' : ''">
                                        <td class="p-2 text-center">
                                            <input type="checkbox" name="devolver[]" value="{{ $id }}" x-model="marcados"
                                                   class="w-5 h-5 rounded border-gray-300 text-green-600 focus:ring-green-500">
                                        </td>
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
                                        <td class="p-2">
                                            @if (count($detalle->lista_piezas))
                                                <div class="flex flex-wrap gap-1" :class="marcados.includes('{{ $id }}') ? '' : 'opacity-40 pointer-events-none'">
                                                    @foreach ($detalle->lista_piezas as $pieza)
                                                        <label class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded border bg-white text-xs cursor-pointer">
                                                            <input type="checkbox" name="piezas[{{ $id }}][]" value="{{ $pieza }}"
                                                                   @checked($piezasViejas === null || in_array($pieza, $piezasViejas))
                                                                   class="rounded border-gray-300 text-green-600 focus:ring-green-500">
                                                            {{ $pieza }}
                                                        </label>
                                                    @endforeach
                                                </div>
                                                <p class="text-xs text-gray-500 mt-1">Desmarque la pieza que falte. Si es un accesorio se repone y el traje vuelve a disponible.</p>
                                            @else
                                                <span class="text-gray-500">{{ $detalle->cantidad_piezas }} pieza(s)</span>
                                            @endif
                                        </td>
                                        <td class="p-2 text-xs">
                                            <div class="text-gray-500 mb-1">Salió: <strong>{{ $detalle->estado_salida ?? 'Sin registrar' }}</strong></div>
                                            <select name="estado_retorno[{{ $id }}]" x-model="estados['{{ $id }}']"
                                                    @change="reparacion['{{ $id }}'] = estados['{{ $id }}'] === 'Roto'"
                                                    :disabled="!marcados.includes('{{ $id }}')"
                                                    :class="volvioPeor('{{ $id }}') ? 'border-red-500 bg-red-50 text-red-800' : ''"
                                                    class="w-full border rounded p-1.5 text-sm disabled:bg-gray-100">
                                                @foreach (\App\Models\DetalleAlquiler::ESTADOS_RETORNO as $estadoRetorno)
                                                    <option value="{{ $estadoRetorno }}">{{ $estadoRetorno }}</option>
                                                @endforeach
                                            </select>
                                            <p x-show="marcados.includes('{{ $id }}') && volvioPeor('{{ $id }}')" class="text-red-600 mt-1">
                                                Volvió peor de lo que salió: considere una penalidad.
                                            </p>
                                            <label x-show="marcados.includes('{{ $id }}')" class="flex items-center gap-1 mt-2 cursor-pointer"
                                                   title="El traje no volverá a disponible hasta marcarlo como reparado en Trajes">
                                                <input type="checkbox" name="reparacion[{{ $id }}]" value="1" x-model="reparacion['{{ $id }}']"
                                                       class="rounded border-gray-300 text-red-600 focus:ring-red-500">
                                                <span :class="reparacion['{{ $id }}'] ? 'text-red-700 font-semibold' : 'text-gray-600'">Enviar a reparación</span>
                                            </label>
                                            <input type="text" name="motivo_reparacion[{{ $id }}]" x-show="marcados.includes('{{ $id }}') && reparacion['{{ $id }}']"
                                                   value="{{ old("motivo_reparacion.$id") }}" placeholder="¿Qué tiene? (ej: pollera rasgada)"
                                                   class="w-full border border-red-300 rounded p-1.5 text-xs mt-1">
                                        </td>
                                        <td class="p-2 text-xs">
                                            @forelse ($detalle->garantias->where('estado', 'entregada') as $garantia)
                                                <div>{{ $garantia->resumen }}</div>
                                            @empty
                                                <span class="text-gray-400">{{ $alquiler->esGrupal() ? 'Ninguna propia' : '—' }}</span>
                                            @endforelse
                                        </td>
                                        <td class="p-2">
                                            <input type="number" step="0.01" min="0" name="penalidad[{{ $id }}]" x-model="penalidades['{{ $id }}']"
                                                   :disabled="!marcados.includes('{{ $id }}')" placeholder="0"
                                                   class="w-full border rounded p-1.5 text-sm disabled:bg-gray-100">
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Garantías generales / del grupo -->
                    @php $generalesRetenidas = $alquiler->garantiasGenerales->where('estado', 'entregada'); @endphp
                    @if ($generalesRetenidas->isNotEmpty())
                        <div class="mb-6 p-4 rounded-lg border"
                             :class="marcados.length === totalPendientes ? 'bg-green-50 border-green-300' : 'bg-gray-50'">
                            <p class="font-semibold mb-1">{{ $alquiler->esGrupal() ? 'Garantías del grupo' : 'Garantías' }}</p>
                            <ul class="text-sm list-disc list-inside">
                                @foreach ($generalesRetenidas as $garantia)
                                    <li>{{ $garantia->resumen }}</li>
                                @endforeach
                            </ul>
                            <p class="text-xs mt-2" x-show="marcados.length === totalPendientes">
                                Con esta devolución vuelven todos los trajes: <strong>se entregan estas garantías</strong>.
                            </p>
                            <p class="text-xs mt-2 text-gray-500" x-show="marcados.length < totalPendientes">
                                Se entregan cuando vuelva el último traje (faltarán <span x-text="totalPendientes - marcados.length"></span>).
                            </p>
                        </div>
                    @endif

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-4">
                        <div>
                            <label class="block font-semibold mb-1">Fecha de devolución</label>
                            <input type="date" name="fecha_devolucion" value="{{ old('fecha_devolucion', date('Y-m-d')) }}" class="w-full border rounded p-2">
                        </div>
                        <div>
                            <label class="block font-semibold mb-1">Penalidad total</label>
                            <div class="border rounded p-2 bg-gray-50 font-semibold" x-text="'Bs ' + totalPenalidad().toFixed(2)"></div>
                        </div>
                        <div>
                            <label class="block font-semibold mb-1">Forma de pago de la penalidad</label>
                            <select name="forma_pago_penalidad" class="w-full border rounded p-2" :disabled="totalPenalidad() <= 0">
                                <option value="">-- No aplica --</option>
                                <option value="Efectivo" @selected(old('forma_pago_penalidad') === 'Efectivo')>Efectivo</option>
                                <option value="QR" @selected(old('forma_pago_penalidad') === 'QR')>Pago QR</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="block font-semibold mb-1">Observaciones (ej: daño, atraso)</label>
                        <textarea name="observaciones" class="w-full border rounded p-2">{{ old('observaciones') }}</textarea>
                    </div>

                    <button type="submit" :disabled="!marcados.length"
                            class="bg-green-700 hover:bg-green-800 disabled:opacity-50 text-white px-5 py-2 rounded-lg font-semibold shadow transition"
                            x-text="marcados.length === totalPendientes ? 'Confirmar devolución completa' : 'Registrar devolución de ' + marcados.length + ' traje(s)'">
                    </button>
                    <a href="{{ route('alquileres.show', $alquiler->id) }}" class="ml-2 text-gray-600">Cancelar</a>
                </form>

                <!-- Ya devueltos -->
                @if ($devueltos->isNotEmpty())
                    <h3 class="font-bold text-lg mt-8 mb-2 text-gray-700">Ya devueltos</h3>
                    <table class="w-full text-sm text-gray-600">
                        @foreach ($devueltos as $detalle)
                            <tr class="border-t">
                                @if ($alquiler->esGrupal())
                                    <td class="p-2">{{ $detalle->persona }}</td>
                                @endif
                                <td class="p-2">{{ $detalle->cantidad }} × {{ $detalle->traje->nombre ?? 'Traje eliminado' }}</td>
                                <td class="p-2">{{ $detalle->devolucion?->fecha_devolucion_formateada }}</td>
                                <td class="p-2">
                                    @if ($detalle->piezas_faltantes)
                                        <span class="text-red-600">Faltó: {{ str_replace(',', ', ', $detalle->piezas_faltantes) }}</span>
                                    @else
                                        Completo
                                    @endif
                                    @if ($detalle->estado_retorno)
                                        · <span class="{{ $detalle->volvioPeor() ? 'text-red-600' : '' }}">{{ $detalle->estado_retorno }}</span>
                                    @endif
                                    @if ($detalle->penalidad > 0)
                                        · Bs {{ number_format($detalle->penalidad, 2) }}
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </table>
                @endif

            </div>
        </div>
    </div>
</x-app-layout>
