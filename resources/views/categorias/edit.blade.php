<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Editar Categoría
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">

                <form action="{{ route('categorias.update', $categoria->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-4">
                        <label class="block font-semibold mb-1">Nombre</label>
                        <input type="text" name="nombre" value="{{ old('nombre', $categoria->nombre) }}" class="w-full border rounded p-2">
                        @error('nombre')
                            <p class="text-red-600 text-sm">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label class="block font-semibold mb-1">Descripción</label>
                        <textarea name="descripcion" class="w-full border rounded p-2">{{ old('descripcion', $categoria->descripcion) }}</textarea>
                    </div>

                    <div class="mb-4">
                        <label class="block font-semibold mb-1">Estado</label>
                        <select name="estado" class="w-full border rounded p-2">
                            <option value="1" @selected(old('estado', $categoria->estado) == 1)>Activo</option>
                            <option value="0" @selected(old('estado', $categoria->estado) == 0)>Inactivo</option>
                        </select>
                        <p class="text-gray-500 text-xs mt-1">
                            Una categoría inactiva no aparece al registrar trajes, y sus trajes no se ofrecen en nuevos alquileres
                            (por ejemplo, fuera de temporada). Los trajes alquilados se pueden devolver normalmente.
                        </p>
                        @error('estado')
                            <p class="text-red-600 text-sm">{{ $message }}</p>
                        @enderror
                    </div>

                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">
                        Actualizar
                    </button>
                    <a href="{{ route('categorias.index') }}" class="ml-2 text-gray-600">Cancelar</a>
                </form>

            </div>
        </div>
    </div>
</x-app-layout>