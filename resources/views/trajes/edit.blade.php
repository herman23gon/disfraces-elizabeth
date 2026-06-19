<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Editar Traje
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">

                <form action="{{ route('trajes.update', $traje->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-4">
                        <label class="block font-semibold mb-1">Categoría</label>
                        <select name="categoria_id" class="w-full border rounded p-2">
                            @foreach ($categorias as $categoria)
                                <option value="{{ $categoria->id }}" {{ old('categoria_id', $traje->categoria_id) == $categoria->id ? 'selected' : '' }}>
                                    {{ $categoria->nombre }}
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
                        <label class="block font-semibold mb-1">Cantidad disponible</label>
                        <input type="number" name="cantidad_disponible" value="{{ old('cantidad_disponible', $traje->cantidad_disponible) }}" class="w-full border rounded p-2">
                        @error('cantidad_disponible')
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

                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">
                        Actualizar
                    </button>
                    <a href="{{ route('trajes.index') }}" class="ml-2 text-gray-600">Cancelar</a>
                </form>

            </div>
        </div>
    </div>
</x-app-layout>