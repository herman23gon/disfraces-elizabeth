<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Clientes
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

                <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
                    @can('clientes.crear')
                    <a href="{{ route('clientes.create') }}" class="bg-green-700 hover:bg-green-800 text-white px-5 py-2 rounded-lg font-semibold shadow transition">
                        + Nuevo Cliente
                    </a>
                    @endcan

                    <div class="flex flex-wrap gap-2 text-sm">
                        <span class="px-3 py-1 rounded-full bg-gray-100 text-gray-700">{{ $clientes->count() }} clientes</span>
                        <span class="px-3 py-1 rounded-full bg-orange-100 text-orange-800">{{ $clientes->where('pendientes', '>', 0)->count() }} con trajes pendientes</span>
                        <span class="px-3 py-1 rounded-full bg-red-100 text-red-800">{{ $clientes->where('vencidos', '>', 0)->count() }} con alquileres vencidos</span>
                    </div>
                </div>

                <!-- Buscador -->
                <form method="GET" action="{{ route('clientes.index') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-3 mb-5 p-4 bg-gray-50 rounded-lg">
                    <input type="text" name="q" value="{{ request('q') }}" placeholder="Buscar por nombre o celular…"
                           class="sm:col-span-2 rounded-md border-gray-300 focus:border-green-600 focus:ring-green-600 text-sm">
                    <select name="filtro" class="rounded-md border-gray-300 focus:border-green-600 focus:ring-green-600 text-sm">
                        <option value="">Todos los clientes</option>
                        <option value="pendientes" @selected(request('filtro') === 'pendientes')>Con trajes pendientes</option>
                        <option value="vencidos" @selected(request('filtro') === 'vencidos')>Con alquileres vencidos</option>
                    </select>
                    <div class="flex gap-2">
                        <button type="submit" class="flex-1 bg-green-700 hover:bg-green-800 text-white px-4 py-2 rounded-md text-sm">Buscar</button>
                        <a href="{{ route('clientes.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-md text-sm">Limpiar</a>
                    </div>
                </form>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-green-50 text-green-900">
                            <tr>
                                <th class="p-2 text-left">Nombre</th>
                                <th class="p-2 text-left">Celular</th>
                                <th class="p-2 text-center">Alquileres</th>
                                <th class="p-2 text-center">Último alquiler</th>
                                <th class="p-2 text-center">Trajes pendientes</th>
                                <th class="p-2 text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($clientes as $cliente)
                                @php
                                    $pendiente = $cliente->alquileres->first();
                                    $mensaje = $pendiente ? $cliente->mensajeRecordatorio($pendiente) : null;
                                @endphp
                                <tr class="border-t hover:bg-gray-50 {{ $cliente->vencidos > 0 ? 'bg-red-50/40' : '' }}">
                                    <td class="p-2">
                                        <a href="{{ route('clientes.show', $cliente->id) }}" class="font-semibold text-gray-800 hover:text-green-700 hover:underline">
                                            {{ $cliente->nombre }}
                                        </a>
                                    </td>
                                    <td class="p-2 whitespace-nowrap">
                                        <span class="font-mono">{{ $cliente->telefono }}</span>
                                        <a href="{{ $cliente->whatsappUrl($mensaje) }}" target="_blank" rel="noopener"
                                           title="{{ $mensaje ? 'Enviar recordatorio por WhatsApp' : 'Abrir chat de WhatsApp' }}"
                                           class="inline-flex items-center gap-1 ml-2 px-2 py-0.5 rounded text-xs font-semibold {{ $mensaje ? 'bg-green-600 text-white hover:bg-green-700' : 'border border-green-600 text-green-700 hover:bg-green-50' }}">
                                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2a10 10 0 0 0-8.6 15.1L2 22l5-1.3A10 10 0 1 0 12 2zm0 18.2a8.2 8.2 0 0 1-4.2-1.2l-.3-.2-3 .8.8-2.9-.2-.3A8.2 8.2 0 1 1 12 20.2zm4.5-6.1c-.2-.1-1.5-.7-1.7-.8-.2-.1-.4-.1-.6.1l-.8 1c-.1.2-.3.2-.5.1a6.7 6.7 0 0 1-3.3-2.9c-.3-.4.3-.4.8-1.4.1-.2 0-.3 0-.4l-.8-1.8c-.2-.5-.4-.4-.6-.4h-.5a1 1 0 0 0-.7.3 3 3 0 0 0-.9 2.2 5.2 5.2 0 0 0 1.1 2.7 11.8 11.8 0 0 0 4.5 4c1.7.7 2.3.8 3.2.6.5-.1 1.5-.6 1.7-1.2.2-.6.2-1.1.1-1.2l-.5-.3z"/></svg>
                                            {{ $mensaje ? 'Recordar' : 'WhatsApp' }}
                                        </a>
                                    </td>
                                    <td class="p-2 text-center">{{ $cliente->alquileres_count }}</td>
                                    <td class="p-2 text-center whitespace-nowrap">
                                        {{ $cliente->ultimo_alquiler ? \Carbon\Carbon::parse($cliente->ultimo_alquiler)->format('d/m/Y') : '—' }}
                                    </td>
                                    <td class="p-2 text-center whitespace-nowrap">
                                        @if ($cliente->vencidos > 0)
                                            <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-800">
                                                {{ $cliente->pendientes }} pendiente(s) · {{ $cliente->vencidos }} vencido(s)
                                            </span>
                                        @elseif ($cliente->pendientes > 0)
                                            <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-orange-100 text-orange-800">
                                                {{ $cliente->pendientes }} pendiente(s)
                                            </span>
                                        @else
                                            <span class="text-gray-400 text-xs">Ninguno</span>
                                        @endif
                                    </td>
                                    <td class="p-2 text-right whitespace-nowrap">
                                        <a href="{{ route('clientes.show', $cliente->id) }}" class="text-green-700 hover:underline">Historial</a>

                                        @can('clientes.editar')
                                        <a href="{{ route('clientes.edit', $cliente->id) }}" class="text-blue-600 hover:underline ml-2">Editar</a>
                                        @endcan

                                        @can('clientes.eliminar')
                                        <form action="{{ route('clientes.destroy', $cliente->id) }}" method="POST" class="inline" onsubmit="return confirm('¿Eliminar este cliente?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:underline ml-2">Eliminar</button>
                                        </form>
                                        @endcan
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="p-6 text-center text-gray-500">No se encontraron clientes.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>
