<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Usuarios
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
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

                <a href="{{ route('usuarios.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded mb-4 inline-block">
                    + Nuevo Usuario
                </a>

                <div class="overflow-x-auto">
                <table class="w-full mt-4 border text-sm">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="p-2 text-left">Nombre</th>
                            <th class="p-2 text-left">Email</th>
                            <th class="p-2 text-left">Rol</th>
                            <th class="p-2 text-left">Estado</th>
                            <th class="p-2 text-left">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($usuarios as $usuario)
                            <tr class="border-t {{ $usuario->activo ? '' : 'bg-gray-50 text-gray-500' }}">
                                <td class="p-2">{{ $usuario->name }}</td>
                                <td class="p-2">{{ $usuario->email }}</td>
                                <td class="p-2 capitalize">{{ $usuario->getRoleNames()->first() ?? 'Sin rol' }}</td>
                                <td class="p-2">
                                    @if ($usuario->activo)
                                        <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-800">Activo</span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-800">Inactivo</span>
                                    @endif
                                </td>
                                <td class="p-2 whitespace-nowrap">
                                    @if ($usuario->id !== auth()->id())
                                        <form action="{{ route('usuarios.toggle-activo', $usuario->id) }}" method="POST" class="inline"
                                              onsubmit="return confirm('¿{{ $usuario->activo ? 'Deshabilitar' : 'Habilitar' }} a este usuario?')">
                                            @csrf
                                            @method('PATCH')
                                            @if ($usuario->activo)
                                                <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded text-xs font-semibold">Deshabilitar</button>
                                            @else
                                                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded text-xs font-semibold">Habilitar</button>
                                            @endif
                                        </form>

                                        {{-- Eliminar definitivamente solo si no tiene movimientos registrados --}}
                                        @if ($usuario->alquileres_count == 0 && $usuario->devoluciones_count == 0)
                                            <form action="{{ route('usuarios.destroy', $usuario->id) }}" method="POST" class="inline" onsubmit="return confirm('¿Eliminar definitivamente este usuario? Esta acción no se puede deshacer.')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:underline text-xs ml-2">Eliminar</button>
                                            </form>
                                        @endif
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
    </div>
</x-app-layout>
