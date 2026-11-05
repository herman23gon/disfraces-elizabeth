<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Nuevo Cliente
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-md sm:rounded-2xl p-8">

                <form action="{{ route('clientes.store') }}" method="POST">
                    @csrf

                    <div class="mb-4">
                        <label class="block font-semibold mb-1">Nombre</label>
                        <input type="text" name="nombre" value="{{ old('nombre') }}" class="w-full border border-gray-300 rounded-lg p-2 focus:border-green-600 focus:ring-2 focus:ring-green-200 focus:outline-none">
                        @error('nombre')
                            <p class="text-red-600 text-sm">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label class="block font-semibold mb-1">Celular</label>
                        <input type="tel" inputmode="numeric" name="telefono" placeholder="Ej: 71234567 (8 dígitos, empieza con 5, 6 o 7)" value="{{ old('telefono') }}" class="w-full border border-gray-300 rounded-lg p-2 focus:border-green-600 focus:ring-2 focus:ring-green-200 focus:outline-none">
                        @error('telefono')
                            <p class="text-red-600 text-sm">{{ $message }}</p>
                        @enderror
                    </div>

                    <button type="submit" class="bg-green-700 hover:bg-green-800 text-white px-5 py-2 rounded-lg font-semibold shadow transition">
                        Guardar
                    </button>
                    <a href="{{ route('clientes.index') }}" class="ml-2 text-gray-500 hover:text-gray-700">Cancelar</a>
                </form>

            </div>
        </div>
    </div>
</x-app-layout>
