<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Bitácora
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">

                @if ($errors->any())
                    <div class="mb-4 p-4 bg-red-100 text-red-700 rounded">
                        {{ $errors->first() }}
                    </div>
                @endif

                <!-- Filtros -->
                <form action="{{ route('bitacoras.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-4 gap-3 items-end mb-6">
                    <div>
                        <label for="desde" class="block text-sm text-gray-600 mb-1">Desde</label>
                        <input type="date" id="desde" name="desde" value="{{ request('desde') }}"
                               class="w-full rounded-md border-gray-300 focus:border-green-600 focus:ring-green-600">
                    </div>
                    <div>
                        <label for="hasta" class="block text-sm text-gray-600 mb-1">Hasta</label>
                        <input type="date" id="hasta" name="hasta" value="{{ request('hasta') }}"
                               class="w-full rounded-md border-gray-300 focus:border-green-600 focus:ring-green-600">
                    </div>
                    <div>
                        <label for="user_id" class="block text-sm text-gray-600 mb-1">Usuario</label>
                        <select id="user_id" name="user_id"
                                class="w-full rounded-md border-gray-300 focus:border-green-600 focus:ring-green-600">
                            <option value="">Todos</option>
                            @foreach ($usuarios as $usuario)
                                <option value="{{ $usuario->id }}" @selected(request('user_id') == $usuario->id)>{{ $usuario->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex gap-2">
                        <button type="submit" class="bg-green-700 hover:bg-green-800 text-white px-4 py-2 rounded">
                            Filtrar
                        </button>
                        <a href="{{ route('bitacoras.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded">
                            Limpiar
                        </a>
                    </div>
                </form>

                <div class="overflow-x-auto">
                    <table class="w-full border text-sm">
                        <thead class="bg-green-50 text-green-900">
                            <tr>
                                <th class="p-2 text-left whitespace-nowrap">Fecha/Hora</th>
                                <th class="p-2 text-left">Usuario</th>
                                <th class="p-2 text-left">Módulo</th>
                                <th class="p-2 text-left">Acción</th>
                                <th class="p-2 text-left">Descripción</th>
                                <th class="p-2 text-left">IP</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($bitacoras as $bitacora)
                                <tr class="border-t">
                                    <td class="p-2 whitespace-nowrap">{{ $bitacora->fecha_hora_formateada }}</td>
                                    <td class="p-2">{{ $bitacora->user->name ?? 'Usuario eliminado' }}</td>
                                    <td class="p-2">{{ $bitacora->modulo }}</td>
                                    <td class="p-2">
                                        @php
                                            $colores = [
                                                'Crear' => 'bg-green-100 text-green-800',
                                                'Editar' => 'bg-blue-100 text-blue-800',
                                                'Eliminar' => 'bg-red-100 text-red-800',
                                                'Habilitar' => 'bg-green-100 text-green-800',
                                                'Deshabilitar' => 'bg-orange-100 text-orange-800',
                                            ];
                                        @endphp
                                        <span class="px-2 py-0.5 rounded text-xs font-semibold {{ $colores[$bitacora->accion] ?? 'bg-gray-100 text-gray-800' }}">
                                            {{ $bitacora->accion }}
                                        </span>
                                    </td>
                                    <td class="p-2">{{ $bitacora->descripcion }}</td>
                                    <td class="p-2 whitespace-nowrap">{{ $bitacora->ip }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="p-4 text-center text-gray-500">No hay registros en la bitácora.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $bitacoras->links() }}
                </div>

            </div>
        </div>
    </div>
</x-app-layout>
