<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Editar Traje
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if (session('success'))
                <div class="p-4 bg-green-100 text-green-700 rounded">
                    {{ session('success') }}
                </div>
            @endif

            <!-- Resumen de stock -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                <div class="bg-white shadow-sm rounded-lg p-4 border-l-4 border-gray-400">
                    <p class="text-xs text-gray-500">Total en tienda</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $traje->cantidad_total }}</p>
                </div>
                <div class="bg-white shadow-sm rounded-lg p-4 border-l-4 border-orange-400">
                    <p class="text-xs text-gray-500">Alquilados ahora</p>
                    <p class="text-2xl font-bold text-orange-600">{{ $alquilados }}</p>
                </div>
                <div class="bg-white shadow-sm rounded-lg p-4 border-l-4 border-red-500">
                    <p class="text-xs text-gray-500">En reparación</p>
                    <p class="text-2xl font-bold text-red-600">{{ $traje->cantidad_reparacion }}</p>
                </div>
                <div class="bg-white shadow-sm rounded-lg p-4 border-l-4 border-green-600">
                    <p class="text-xs text-gray-500">Disponibles</p>
                    <p class="text-2xl font-bold text-green-700">{{ $traje->cantidad_disponible }}</p>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">

                <form action="{{ route('trajes.update', $traje->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-4">
                        <label class="block font-semibold mb-1">Categoría</label>
                        <select name="categoria_id" class="w-full border rounded p-2">
                            @foreach ($categorias as $categoria)
                                <option value="{{ $categoria->id }}" {{ old('categoria_id', $traje->categoria_id) == $categoria->id ? 'selected' : '' }}>
                                    {{ $categoria->nombre }}{{ $categoria->estado ? '' : ' (inactiva)' }}
                                </option>
                            @endforeach
                        </select>
                        @error('categoria_id')
                            <p class="text-red-600 text-sm">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label class="block font-semibold mb-1">Nombre del traje</label>
                        <input type="text" name="nombre" value="{{ old('nombre', $traje->nombre) }}" class="w-full border rounded p-2">
                        @error('nombre')
                            <p class="text-red-600 text-sm">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label class="block font-semibold mb-1">Talla</label>
                        <input type="text" name="talla" value="{{ old('talla', $traje->talla) }}" class="w-full border rounded p-2">
                    </div>

                    <div class="mb-4">
                        <label class="block font-semibold mb-1">Cantidad de piezas</label>
                        <input type="number" name="cantidad_piezas" value="{{ old('cantidad_piezas', $traje->cantidad_piezas) }}" class="w-full border rounded p-2">
                        @error('cantidad_piezas')
                            <p class="text-red-600 text-sm">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label class="block font-semibold mb-1">Descripción</label>
                        <textarea name="descripcion" class="w-full border rounded p-2">{{ old('descripcion', $traje->descripcion) }}</textarea>
                    </div>

                    <div class="mb-4">
                        <label class="block font-semibold mb-1">Cantidad total</label>
                        <input type="number" name="cantidad_total" min="{{ $alquilados + $traje->cantidad_reparacion }}" value="{{ old('cantidad_total', $traje->cantidad_total) }}" class="w-full border rounded p-2">
                        <p class="text-gray-500 text-xs mt-1">
                            Unidades que tiene la tienda. Súbalo si compró más; el disponible se recalcula solo.
                            Para unidades dañadas o perdidas use <strong>Dar de baja</strong> (queda registrado el motivo).
                        </p>
                        @error('cantidad_total')
                            <p class="text-red-600 text-sm">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label class="block font-semibold mb-1">Precio de referencia (Bs.)</label>
                        <input type="number" step="0.01" name="precio_referencia" value="{{ old('precio_referencia', $traje->precio_referencia) }}" class="w-full border rounded p-2">
                        @error('precio_referencia')
                            <p class="text-red-600 text-sm">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label class="block font-semibold mb-1">Estado</label>
                        <select name="estado" class="w-full border rounded p-2">
                            <option value="1" @selected(old('estado', $traje->estado) == 1)>Activo</option>
                            <option value="0" @selected(old('estado', $traje->estado) == 0)>Inactivo</option>
                        </select>
                        <p class="text-gray-500 text-xs mt-1">
                            Un traje inactivo no se ofrece en nuevos alquileres (por ejemplo, en reparación o lavandería).
                            Si está alquilado se puede devolver normalmente.
                        </p>
                        @if (!$traje->categoria->estado)
                            <p class="text-yellow-700 text-xs mt-1">
                                La categoría "{{ $traje->categoria->nombre }}" está inactiva: este traje no se ofrece en alquileres aunque esté activo.
                            </p>
                        @endif
                    </div>

                    <button type="submit" class="bg-green-700 hover:bg-green-800 text-white px-4 py-2 rounded">
                        Actualizar
                    </button>
                    <a href="{{ route('trajes.index') }}" class="ml-2 text-gray-600">Cancelar</a>
                </form>

            </div>

            <!-- Reparación -->
            @php $abiertas = $traje->reparaciones->where('estado', 'en_reparacion'); @endphp
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="font-semibold text-gray-800 mb-1">Reparación</h3>
                <p class="text-sm text-gray-500 mb-4">
                    Unidades con el traje principal dañado. No se alquilan hasta marcarlas como reparadas.
                    Llegan aquí desde la devolución ("Enviar a reparación") o se envían manualmente.
                </p>

                @if ($abiertas->isNotEmpty())
                    <table class="w-full border text-sm mb-4">
                        <thead class="bg-red-50 text-red-900">
                            <tr>
                                <th class="p-2 text-left">Desde</th>
                                <th class="p-2 text-left">Cant.</th>
                                <th class="p-2 text-left">Motivo</th>
                                <th class="p-2 text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($abiertas as $reparacion)
                                <tr class="border-t align-top">
                                    <td class="p-2 whitespace-nowrap">
                                        {{ $reparacion->created_at->format('d/m/Y') }}
                                        <div class="text-xs text-gray-500">{{ $reparacion->created_at->diffForHumans() }}</div>
                                    </td>
                                    <td class="p-2">{{ $reparacion->cantidad }}</td>
                                    <td class="p-2">{{ $reparacion->motivo }}</td>
                                    <td class="p-2 text-right whitespace-nowrap">
                                        <form action="{{ route('reparaciones.reparado', $reparacion->id) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded text-xs font-semibold">Reparado</button>
                                        </form>
                                        <form action="{{ route('reparaciones.baja', $reparacion->id) }}" method="POST" class="inline"
                                              onsubmit="return confirm('¿Dar de baja esta unidad? Se descontará del total.')">
                                            @csrf
                                            <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded text-xs font-semibold ml-1">Sin arreglo: dar de baja</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <p class="text-sm text-gray-500 mb-4">No hay unidades en reparación.</p>
                @endif

                @if ($traje->cantidad_disponible > 0)
                    <details class="text-sm">
                        <summary class="cursor-pointer text-red-700 font-semibold">Enviar una unidad disponible a reparación</summary>
                        <form action="{{ route('trajes.reparacion', $traje->id) }}" method="POST" class="grid grid-cols-1 sm:grid-cols-4 gap-3 mt-3 items-start">
                            @csrf
                            <input type="number" name="cantidad" min="1" max="{{ $traje->cantidad_disponible }}" value="1" class="border rounded p-2" title="Cantidad">
                            <input type="text" name="motivo" placeholder="¿Qué tiene? (ej: cierre roto)" class="sm:col-span-2 border rounded p-2" required>
                            <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded">Enviar a reparación</button>
                        </form>
                    </details>
                @endif

                @php $resueltas = $traje->reparaciones->where('estado', '!=', 'en_reparacion'); @endphp
                @if ($resueltas->isNotEmpty())
                    <h4 class="font-semibold text-sm text-gray-700 mt-6 mb-2">Historial de reparaciones</h4>
                    <table class="w-full border text-sm text-gray-600">
                        @foreach ($resueltas as $reparacion)
                            <tr class="border-t">
                                <td class="p-2 whitespace-nowrap">{{ $reparacion->created_at->format('d/m/Y') }} → {{ $reparacion->fecha_resolucion?->format('d/m/Y') }}</td>
                                <td class="p-2">{{ $reparacion->cantidad }}</td>
                                <td class="p-2">{{ $reparacion->motivo }}</td>
                                <td class="p-2">{{ $reparacion->estado === 'reparado' ? 'Reparado' : 'Dado de baja' }}</td>
                            </tr>
                        @endforeach
                    </table>
                @endif
            </div>

            <!-- Dar de baja -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="font-semibold text-gray-800 mb-1">Dar de baja unidades</h3>
                <p class="text-sm text-gray-500 mb-4">
                    Para unidades dañadas, perdidas o que ya no se alquilarán. Se descuentan del total y del disponible.
                    Solo se pueden dar de baja unidades disponibles; si una está alquilada, primero regístrese su devolución.
                </p>

                @if ($traje->cantidad_disponible > 0)
                    <form action="{{ route('trajes.baja', $traje->id) }}" method="POST"
                          onsubmit="return confirm('¿Dar de baja estas unidades? Se descontarán del stock.')"
                          class="grid grid-cols-1 sm:grid-cols-3 gap-3 items-start">
                        @csrf
                        <div>
                            <label class="block text-sm text-gray-600 mb-1">Cantidad</label>
                            <input type="number" name="cantidad" min="1" max="{{ $traje->cantidad_disponible }}" value="{{ old('cantidad', 1) }}" class="w-full border rounded p-2">
                        </div>
                        <div>
                            <label class="block text-sm text-gray-600 mb-1">Motivo</label>
                            <select name="motivo" class="w-full border rounded p-2">
                                @foreach ($motivos as $motivo)
                                    <option value="{{ $motivo }}" @selected(old('motivo') === $motivo)>{{ $motivo }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm text-gray-600 mb-1">Observación</label>
                            <input type="text" name="observacion" value="{{ old('observacion') }}" placeholder="Ej: máscara rota" class="w-full border rounded p-2">
                        </div>
                        <div class="sm:col-span-3">
                            @foreach (['cantidad', 'motivo', 'observacion'] as $campo)
                                @error($campo)
                                    <p class="text-red-600 text-sm">{{ $message }}</p>
                                @enderror
                            @endforeach
                            <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded text-sm">
                                Dar de baja
                            </button>
                        </div>
                    </form>
                @else
                    <p class="text-sm text-gray-500">No hay unidades disponibles para dar de baja.</p>
                @endif

                @if ($traje->bajas->isNotEmpty())
                    <h4 class="font-semibold text-sm text-gray-700 mt-6 mb-2">Historial de bajas</h4>
                    <table class="w-full border text-sm">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="p-2 text-left">Fecha</th>
                                <th class="p-2 text-left">Cant.</th>
                                <th class="p-2 text-left">Motivo</th>
                                <th class="p-2 text-left">Observación</th>
                                <th class="p-2 text-left">Usuario</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($traje->bajas->sortByDesc('created_at') as $baja)
                                <tr class="border-t">
                                    <td class="p-2 whitespace-nowrap">{{ $baja->created_at->format('d/m/Y') }}</td>
                                    <td class="p-2">{{ $baja->cantidad }}</td>
                                    <td class="p-2">{{ $baja->motivo }}</td>
                                    <td class="p-2">{{ $baja->observacion }}</td>
                                    <td class="p-2">{{ $baja->user->name ?? '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
