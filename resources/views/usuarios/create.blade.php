<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Nuevo Usuario
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-md sm:rounded-2xl p-8">

                <form action="{{ route('usuarios.store') }}" method="POST">
                    @csrf

                    <div class="mb-4">
                        <label class="block font-semibold mb-1">Nombre</label>
                        <input type="text" name="name" value="{{ old('name') }}" class="w-full border border-gray-300 rounded-lg p-2 focus:border-green-600 focus:ring-2 focus:ring-green-200 focus:outline-none">
                        @error('name')
                            <p class="text-red-600 text-sm">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label class="block font-semibold mb-1">Email</label>
                        <input type="email" name="email" value="{{ old('email') }}" class="w-full border border-gray-300 rounded-lg p-2 focus:border-green-600 focus:ring-2 focus:ring-green-200 focus:outline-none">
                        @error('email')
                            <p class="text-red-600 text-sm">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label class="block font-semibold mb-1">Contraseña</label>
                        <input type="password" name="password" class="w-full border border-gray-300 rounded-lg p-2 focus:border-green-600 focus:ring-2 focus:ring-green-200 focus:outline-none">
                        @error('password')
                            <p class="text-red-600 text-sm">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label class="block font-semibold mb-1">Confirmar Contraseña</label>
                        <input type="password" name="password_confirmation" class="w-full border border-gray-300 rounded-lg p-2 focus:border-green-600 focus:ring-2 focus:ring-green-200 focus:outline-none">
                    </div>

                    <div class="mb-4">
                        <label class="block font-semibold mb-1">Rol</label>
                        <select name="role" class="w-full border border-gray-300 rounded-lg p-2 focus:border-green-600 focus:ring-2 focus:ring-green-200 focus:outline-none">
                            @foreach ($roles as $rol)
                                <option value="{{ $rol->name }}">{{ ucfirst($rol->name) }}</option>
                            @endforeach
                        </select>
                        @error('role')
                            <p class="text-red-600 text-sm">{{ $message }}</p>
                        @enderror
                    </div>

                    <button type="submit" class="bg-green-700 hover:bg-green-800 text-white px-5 py-2 rounded-lg font-semibold shadow transition">
                        Guardar
                    </button>
                    <a href="{{ route('usuarios.index') }}" class="ml-2 text-gray-500 hover:text-gray-700">Cancelar</a>
                </form>

            </div>
        </div>
    </div>
</x-app-layout>
