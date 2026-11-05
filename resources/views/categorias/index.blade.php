<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Categorías
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
                <div class="flex flex-wrap gap-2 mb-4">
                    @can('categorias.crear')
                    <a href="{{ route('categorias.create') }}" class="bg-green-700 hover:bg-green-800 text-white px-5 py-2 rounded-lg font-semibold shadow transition">
                        + Nueva Categoría
                    </a>
                    @endcan
                    <a href="{{ route('categorias.pdf') }}" target="_blank"
                       class="border border-green-700 text-green-700 hover:bg-green-50 px-5 py-2 rounded-lg font-semibold transition">
                        🖨️ Imprimir lista (PDF)
                    </a>
                </div>

                <table class="w-full mt-4 text-sm">
                    <thead class="bg-green-50 text-green-900">
                        <tr>
                            <th class="p-2 text-left">Nombre</th>
                            <th class="p-2 text-left">Descripción</th>
                            <th class="p-2 text-center">Trajes</th>
                            <th class="p-2 text-center">Unidades</th>
                            <th class="p-2 text-center">Estado</th>
                            <th class="p-2 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($categorias as $categoria)
                            <tr class="border-t align-top hover:bg-gray-50 {{ $categoria->estado ? '' : 'text-gray-400' }}">
                                <td class="p-2 font-semibold">{{ $categoria->nombre }}</td>
                                <td class="p-2">{{ $categoria->descripcion ?: '—' }}</td>
                                <td class="p-2 text-center">
                                    @can('trajes.ver')
                                        <a href="{{ route('trajes.index', ['categoria_id' => $categoria->id]) }}" class="text-green-700 hover:underline" title="Ver los trajes de esta categoría">{{ $categoria->trajes_count }}</a>
                                    @else
                                        {{ $categoria->trajes_count }}
                                    @endcan
                                </td>
                                <td class="p-2 text-center">{{ (int) $categoria->unidades }}</td>
                                <td class="p-2 text-center">
                                    <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $categoria->estado ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ $categoria->estado ? 'Activa' : 'Inactiva' }}
                                    </span>
                                </td>
                                <td class="p-2 text-right whitespace-nowrap">
                                    @can('categorias.editar')
                                    <a href="{{ route('categorias.edit', $categoria->id) }}" class="text-blue-600">Editar</a>
                                    @endcan

                                    @can('categorias.eliminar')
                                    <form action="{{ route('categorias.destroy', $categoria->id) }}" method="POST" class="inline" onsubmit="return confirm('¿Eliminar esta categoría?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 ml-2">Eliminar</button>
                                    </form>
                                    @endcan
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

            </div>
        </div>
    </div>
</x-app-layout>