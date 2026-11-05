<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Roles y Permisos
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

                @if ($errors->any())
                    <div class="mb-4 p-4 bg-red-100 text-red-700 rounded">
                        {{ $errors->first() }}
                    </div>
                @endif

                <p class="text-sm text-gray-600 mb-4">
                    Marque qué acciones puede realizar cada rol en cada módulo.
                    El rol <strong>Administrador</strong> tiene todos los permisos y no se pueden modificar.
                    Los empleados con permisos personalizados (desde Usuarios → Permisos) no se ven afectados por esta tabla.
                </p>

                <form action="{{ route('roles-permisos.update') }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="overflow-x-auto">
                        <table class="w-full border text-sm">
                            <thead class="bg-green-50 text-green-900">
                                <tr>
                                    <th rowspan="2" class="p-2 text-left border">Módulo</th>
                                    <th colspan="{{ count($acciones) }}" class="p-2 text-center border">Administrador</th>
                                    <th colspan="{{ count($acciones) }}" class="p-2 text-center border">Empleado</th>
                                </tr>
                                <tr>
                                    @foreach ($acciones as $accion)
                                        <th class="p-2 text-center border font-medium">{{ $accion }}</th>
                                    @endforeach
                                    @foreach ($acciones as $accion)
                                        <th class="p-2 text-center border font-medium">{{ $accion }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($modulos as $modulo => $datos)
                                    <tr class="border-t">
                                        <td class="p-2 border font-semibold">{{ $datos['nombre'] }}</td>

                                        {{-- Administrador: siempre todos los permisos --}}
                                        @foreach ($acciones as $accion => $nombreAccion)
                                            <td class="p-2 border text-center bg-gray-50">
                                                @if (in_array($accion, $datos['acciones']))
                                                    <input type="checkbox" checked disabled
                                                           class="rounded border-gray-300 text-green-600 opacity-60 cursor-not-allowed"
                                                           title="El administrador siempre tiene este permiso">
                                                @else
                                                    <span class="text-gray-300">—</span>
                                                @endif
                                            </td>
                                        @endforeach

                                        {{-- Empleado: configurable --}}
                                        @foreach ($acciones as $accion => $nombreAccion)
                                            @php $permiso = $modulo . '.' . $accion; @endphp
                                            <td class="p-2 border text-center">
                                                @if (in_array($accion, $datos['acciones']))
                                                    <input type="checkbox" name="permisos[]" value="{{ $permiso }}"
                                                           @checked(in_array($permiso, old('permisos', $permisosEmpleado)))
                                                           class="rounded border-gray-300 text-green-600 focus:ring-green-500"
                                                           title="{{ $nombreAccion }} {{ $datos['nombre'] }}">
                                                @else
                                                    <span class="text-gray-300">—</span>
                                                @endif
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <p class="text-xs text-gray-500 mt-3">
                        Nota: en Reportes solo se usa el permiso <strong>Ver</strong>. En Devoluciones, <strong>Crear</strong> permite registrar la devolución de un alquiler.
                        En Copia de Seguridad, <strong>Generar</strong> permite crear y descargar copias; <strong>Ver</strong> solo muestra la lista.
                    </p>

                    <div class="mt-4">
                        <button type="submit" class="bg-green-700 hover:bg-green-800 text-white px-4 py-2 rounded">
                            Guardar permisos
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</x-app-layout>
