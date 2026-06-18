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

                <a href="{{ route('categorias.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded mb-4 inline-block">
                    + Nueva Categoría
                </a>

                <table class="w-full mt-4 border">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="p-2 text-left">Nombre</th>
                            <th class="p-2 text-left">Descripción</th>
                            <th class="p-2 text-left">Estado</th>
                            <th class="p-2 text-left">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($categorias as $categoria)
                            <tr class="border-t">
                                <td class="p-2">{{ $categoria->nombre }}</td>
                                <td class="p-2">{{ $categoria->descripcion }}</td>
                                <td class="p-2">{{ $categoria->estado ? 'Activo' : 'Inactivo' }}</td>
                                <td class="p-2">
                                    <a href="{{ route('categorias.edit', $categoria->id) }}" class="text-blue-600">Editar</a>

                                    <form action="{{ route('categorias.destroy', $categoria->id) }}" method="POST" class="inline" onsubmit="return confirm('¿Eliminar esta categoría?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 ml-2">Eliminar</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

            </div>
        </div>
    </div>
</x-app-layout>