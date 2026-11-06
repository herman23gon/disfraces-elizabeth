<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Trajes
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
                @if (session('error'))
                    <div class="mb-4 p-4 bg-red-100 text-red-700 rounded">
                        {{ session('error') }}
                    </div>
                @endif

                <!-- Acciones -->
                <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
                    <div class="flex flex-wrap gap-2">
                        @can('trajes.crear')
                        <a href="{{ route('trajes.create') }}" class="bg-green-700 hover:bg-green-800 text-white px-5 py-2 rounded-lg font-semibold shadow transition">
                            + Nuevo Traje
                        </a>
                        @endcan
                        <a href="{{ route('trajes.pdf', request()->only('q', 'categoria_id', 'estado')) }}" target="_blank"
                           class="border border-green-700 text-green-700 hover:bg-green-50 px-5 py-2 rounded-lg font-semibold transition">
                            🖨️ Imprimir inventario (PDF)
                        </a>
                    </div>

                    <!-- Resumen -->
                    <div class="flex flex-wrap gap-2 text-sm">
                        <span class="px-3 py-1 rounded-full bg-gray-100 text-gray-700">{{ $trajes->count() }} trajes</span>
                        <span class="px-3 py-1 rounded-full bg-gray-100 text-gray-700">{{ $trajes->sum('cantidad_total') }} unidades</span>
                        <span class="px-3 py-1 rounded-full bg-orange-100 text-orange-800">{{ $trajes->sum('cantidad_total') - $trajes->sum('cantidad_disponible') - $trajes->sum('cantidad_reparacion') }} alquiladas</span>
                        @if ($trajes->sum('cantidad_reparacion') > 0)
                            <span class="px-3 py-1 rounded-full bg-red-100 text-red-800">{{ $trajes->sum('cantidad_reparacion') }} en reparación</span>
                        @endif
                        <span class="px-3 py-1 rounded-full bg-green-100 text-green-800">{{ $trajes->sum('cantidad_disponible') }} disponibles</span>
                    </div>
                </div>

                <!-- Filtros -->
                <form method="GET" action="{{ route('trajes.index') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-3 mb-5 p-4 bg-gray-50 rounded-lg">
                    <input type="text" name="q" value="{{ request('q') }}" placeholder="Buscar por nombre o pieza…"
                           class="rounded-md border-gray-300 focus:border-green-600 focus:ring-green-600 text-sm">
                    <select name="categoria_id" class="rounded-md border-gray-300 focus:border-green-600 focus:ring-green-600 text-sm">
                        <option value="">Todas las categorías</option>
                        @foreach ($categorias as $categoria)
                            <option value="{{ $categoria->id }}" @selected(request('categoria_id') == $categoria->id)>
                                {{ $categoria->nombre }}{{ $categoria->estado ? '' : ' (inactiva)' }}
                            </option>
                        @endforeach
                    </select>
                    <select name="estado" class="rounded-md border-gray-300 focus:border-green-600 focus:ring-green-600 text-sm">
                        <option value="">Todos los estados</option>
                        <option value="activo" @selected(request('estado') === 'activo')>Activos</option>
                        <option value="inactivo" @selected(request('estado') === 'inactivo')>Inactivos</option>
                    </select>
                    <div class="flex gap-2">
                        <button type="submit" class="flex-1 bg-green-700 hover:bg-green-800 text-white px-4 py-2 rounded-md text-sm">Filtrar</button>
                        <a href="{{ route('trajes.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-md text-sm">Limpiar</a>
                    </div>
                </form>

                <!-- Lista agrupada por categoría -->
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-green-50 text-green-900">
                            <tr>
                                <th class="p-2 text-left">Traje</th>
                                <th class="p-2 text-center">Talla</th>
                                <th class="p-2 text-center">Total</th>
                                <th class="p-2 text-center">Alquilados</th>
                                <th class="p-2 text-center">Reparación</th>
                                <th class="p-2 text-center">Disponibles</th>
                                <th class="p-2 text-right">Precio</th>
                                <th class="p-2 text-center">Estado</th>
                                <th class="p-2 text-right">Acciones</th>
                            </tr>
                        </thead>
                        @forelse ($trajes->groupBy(fn ($t) => $t->categoria->nombre) as $nombreCategoria => $grupo)
                            <tbody>
                                <tr class="bg-gray-100 border-t-2 border-gray-200">
                                    <td colspan="9" class="px-2 py-1.5 font-semibold text-gray-700">
                                        {{ $nombreCategoria }}
                                        <span class="font-normal text-gray-500 text-xs">· {{ $grupo->count() }} trajes · {{ $grupo->sum('cantidad_total') }} unidades</span>
                                        @unless ($grupo->first()->categoria->estado)
                                            <span class="ml-1 px-2 py-0.5 rounded-full text-xs bg-yellow-100 text-yellow-800">Categoría inactiva</span>
                                        @endunless
                                    </td>
                                </tr>
                                @foreach ($grupo as $traje)
                                    @php
                                        $piezas = collect(explode(',', (string) $traje->descripcion))->map(fn ($p) => trim($p))->filter();
                                    @endphp
                                    <tr class="border-t align-top hover:bg-gray-50 {{ $traje->estado ? '' : 'text-gray-400' }}">
                                        <td class="p-2">
                                            <div class="font-semibold {{ $traje->estado ? 'text-gray-800' : '' }}">{{ $traje->nombre }}</div>
                                            <div class="mt-1 flex flex-wrap items-center gap-1 text-xs">
                                                <span class="text-gray-500">{{ $traje->cantidad_piezas }} {{ $traje->cantidad_piezas == 1 ? 'pieza' : 'piezas' }}:</span>
                                                @if ($piezas->count() > 1)
                                                    @foreach ($piezas as $pieza)
                                                        <span class="px-1.5 py-0.5 rounded bg-gray-100 text-gray-700">{{ $pieza }}</span>
                                                    @endforeach
                                                @else
                                                    <span class="text-gray-600">{{ $traje->descripcion ?: '—' }}</span>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="p-2 text-center uppercase">{{ $traje->talla ?: '—' }}</td>
                                        <td class="p-2 text-center">{{ $traje->cantidad_total }}</td>
                                        <td class="p-2 text-center">{{ $traje->cantidad_total - $traje->cantidad_disponible - $traje->cantidad_reparacion }}</td>
                                        <td class="p-2 text-center {{ $traje->cantidad_reparacion > 0 ? 'text-red-600 font-semibold' : 'text-gray-400' }}">{{ $traje->cantidad_reparacion }}</td>
                                        <td class="p-2 text-center font-semibold {{ $traje->cantidad_disponible > 0 ? 'text-green-700' : 'text-red-600' }}">{{ $traje->cantidad_disponible }}</td>
                                        <td class="p-2 text-right whitespace-nowrap">Bs. {{ number_format($traje->precio_referencia, 2) }}</td>
                                        <td class="p-2 text-center whitespace-nowrap">
                                            @if (!$traje->estado)
                                                <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-800">Inactivo</span>
                                            @elseif (!$traje->categoria->estado)
                                                <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-800" title="El traje está activo, pero su categoría no: no se ofrece en alquileres">Categoría inactiva</span>
                                            @else
                                                <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-800">Activo</span>
                                            @endif
                                        </td>
                                        <td class="p-2 text-right whitespace-nowrap">
                                            @can('trajes.editar')
                                            <a href="{{ route('trajes.edit', $traje->id) }}" class="text-blue-600 hover:underline">Editar</a>
                                            @endcan

                                            @can('trajes.eliminar')
                                            <form action="{{ route('trajes.destroy', $traje->id) }}" method="POST" class="inline" onsubmit="return confirm('¿Eliminar este traje?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:underline ml-2">Eliminar</button>
                                            </form>
                                            @endcan
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        @empty
                            <tbody>
                                <tr>
                                    <td colspan="9" class="p-6 text-center text-gray-500">No se encontraron trajes con esos filtros.</td>
                                </tr>
                            </tbody>
                        @endforelse
                    </table>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>
