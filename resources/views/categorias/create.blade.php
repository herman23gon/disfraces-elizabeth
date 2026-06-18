<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Nueva Categoría
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">

                <form action="{{ route('categorias.store') }}" method="POST">
                    @csrf

                    <div class="mb-4">
                        <label class="block font-semibold mb-1">Nombre</label>
                        <input type="text" name="nombre" value="{{ old('nombre') }}" class="w-full border rounded p-2">
                        @error('nombre')
                            <p class="text-red-600 text-sm">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label class="block font-semibold mb-1">Descripción</label>
                        <textarea name="descripcion" class="w-full border rounded p-2">{{ old('descripcion') }}</textarea>
                    </div>

                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">
                        Guardar
                    </button>
                    <a href="{{ route('categorias.index') }}" class="ml-2 text-gray-600">Cancelar</a>
                </form>

            </div>
        </div>
    </div>
</x-app-layout>