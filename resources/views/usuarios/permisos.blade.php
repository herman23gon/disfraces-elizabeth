<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Permisos de {{ $usuario->name }}
            <span class="text-base font-normal text-gray-500">· Rol: {{ $nombreRol }}</span>
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6"
                 x-data="{
                     modo: '{{ old('modo', $usuario->permisos_personalizados ? 'personalizado' : 'rol') }}',
                     permisosRol: @js($permisosRol),
                     copiarDelRol() {
                         this.$refs.tabla.querySelectorAll('input[type=checkbox]').forEach(c => c.checked = this.permisosRol.includes(c.value));
                     }
                 }">

                @if ($errors->any())
                    <div class="mb-4 p-4 bg-red-100 text-red-700 rounded">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form action="{{ route('usuarios.permisos.update', $usuario->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="space-y-2 mb-5 text-sm">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="modo" value="rol" x-model="modo" class="text-green-600 focus:ring-green-500">
                            Usar los permisos del rol {{ $nombreRol }}
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="modo" value="personalizado" x-model="modo" class="text-green-600 focus:ring-green-500">
                            Permisos personalizados para este usuario
                        </label>
                    </div>

                    <p x-show="modo === 'rol'" class="mb-3 text-sm text-gray-500">
                        Este usuario tendrá los mismos permisos que el rol {{ $nombreRol }}, configurados en Roles y Permisos.
                    </p>

                    <div x-ref="tabla" class="overflow-x-auto transition" :class="modo === 'rol' && 'opacity-40 pointer-events-none'">
                        <table class="w-full border text-sm">
                            <thead class="bg-green-50 text-green-900">
                                <tr>
                                    <th class="p-2 text-left border">Módulo</th>
                                    @foreach ($acciones as $nombreAccion)
                                        <th class="p-2 text-center border font-medium">{{ $nombreAccion }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($modulos as $modulo => $datos)
                                    <tr class="border-t">
                                        <td class="p-2 border font-semibold">{{ $datos['nombre'] }}</td>
                                        @foreach ($acciones as $accion => $nombreAccion)
                                            @php $permiso = $modulo . '.' . $accion; @endphp
                                            <td class="p-2 border text-center">
                                                @if (in_array($accion, $datos['acciones']))
                                                    <input type="checkbox" name="permisos[]" value="{{ $permiso }}"
                                                           @checked(in_array($permiso, old('permisos', $usuario->permisos_personalizados ? $permisosUsuario : $permisosRol)))
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

                    <div class="mt-5 flex flex-wrap gap-2">
                        <button type="submit" class="bg-green-700 hover:bg-green-800 text-white px-4 py-2 rounded">
                            Guardar permisos
                        </button>
                        <button type="button" x-show="modo === 'personalizado'" @click="copiarDelRol()"
                                class="border border-green-700 text-green-700 hover:bg-green-50 px-4 py-2 rounded">
                            Copiar del rol {{ $nombreRol }}
                        </button>
                        <a href="{{ route('usuarios.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded">
                            Cancelar
                        </a>
                    </div>
                </form>

            </div>
        </div>
    </div>
</x-app-layout>
