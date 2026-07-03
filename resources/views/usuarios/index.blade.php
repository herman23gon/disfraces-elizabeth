<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Usuarios
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">

                @if (session('success'))
                    <div class="mb-4 p-4 bg-green-100 text-green-700 rounded">
                        {{ session('success') }}
                    </div>
                @endif

                <a href="{{ route('usuarios.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded mb-4 inline-block">
                    + Nuevo Usuario
                </a>

                <table class="w-full mt-4 border text-sm">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="p-2 text-left">Nombre</th>
                            <th class="p-2 text-left">Email</th>
                            <th class="p-2 text-left">Rol</th>
                            <th class="p-2 text-left">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($usuarios as $usuario)
                            <tr class="border-t">
                                <td class="p-2">{{ $usuario->name }}</td>
                                <td class="p-2">{{ $usuario->email }}</td>
                                <td class="p-2">{{ $usuario->getRoleNames()->first() ?? 'Sin rol' }}</td>
                                <td class="p-2">
                                    @if ($usuario->id !== auth()->id())
                                        <form action="{{ route('usuarios.destroy', $usuario->id) }}" method="POST" class="inline" onsubmit="return confirm('¿Eliminar este usuario?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600">Eliminar</button>
                                        </form>
                                    @else
                                        <span class="text-gray-400 text-xs">Tu cuenta</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

            </div>
        </div>
    </div>
</x-app-layout>