<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Nuevo Usuario
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">

                <form action="{{ route('usuarios.store') }}" method="POST">
                    @csrf

                    <div class="mb-4">
                        <label class="block font-semibold mb-1">Nombre</label>
                        <input type="text" name="name" value="{{ old('name') }}" class="w-full border rounded p-2">
                        @error('name')
                            <p class="text-red-600 text-sm">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label class="block font-semibold mb-1">Email</label>
                        <input type="email" name="email" value="{{ old('email') }}" class="w-full border rounded p-2">
                        @error('email')
                            <p class="text-red-600 text-sm">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label class="block font-semibold mb-1">Contraseña</label>
                        <input type="password" name="password" class="w-full border rounded p-2">
                        @error('password')
                            <p class="text-red-600 text-sm">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label class="block font-semibold mb-1">Confirmar Contraseña</label>
                        <input type="password" name="password_confirmation" class="w-full border rounded p-2">
                    </div>

                    <div class="mb-4">
                        <label class="block font-semibold mb-1">Rol</label>
                        <select name="role" class="w-full border rounded p-2">
                            @foreach ($roles as $rol)
                                <option value="{{ $rol->name }}">{{ ucfirst($rol->name) }}</option>
                            @endforeach
                        </select>
                        @error('role')
                            <p class="text-red-600 text-sm">{{ $message }}</p>
                        @enderror
                    </div>

                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">
                        Guardar
                    </button>
                    <a href="{{ route('usuarios.index') }}" class="ml-2 text-gray-600">Cancelar</a>
                </form>

            </div>
        </div>
    </div>
</x-app-layout>