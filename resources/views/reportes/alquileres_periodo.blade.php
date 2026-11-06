<x-app-layout>
    <script src="{{ asset('js/jquery.min.js') }}"></script>
    <script src="{{ asset('js/select2.min.js') }}"></script>
    <link href="{{ asset('css/select2.min.css') }}" rel="stylesheet" />
    <style>
        .select2-container--default .select2-selection--single { border: 1px solid #d1d5db; border-radius: 0.375rem; height: 38px; padding: 0.35rem; }
        .select2-container--default .select2-selection--single .select2-selection__arrow { height: 36px; }
        .select2-container--default .select2-results__option--highlighted[aria-selected] { background-color: #15803d !important; color: #fff !important; }
    </style>

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Reporte: Alquileres por Período
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">

                @if ($errors->any())
                    <div class="mb-4 p-4 bg-red-100 text-red-700 rounded">{{ $errors->first() }}</div>
                @endif

                <!-- Filtros -->
                <form method="GET" class="grid grid-cols-1 sm:grid-cols-6 gap-3 items-end mb-5 p-4 bg-gray-50 rounded-lg text-sm">
                    <div>
                        <label class="block font-semibold mb-1">Desde</label>
                        <input type="date" name="desde" value="{{ $desde }}" class="w-full border-gray-300 rounded-md">
                    </div>
                    <div>
                        <label class="block font-semibold mb-1">Hasta</label>
                        <input type="date" name="hasta" value="{{ $hasta }}" class="w-full border-gray-300 rounded-md">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block font-semibold mb-1">Cliente</label>
                        <select name="cliente_id" id="filtro-cliente" class="w-full">
                            <option value="">Todos los clientes</option>
                            @foreach ($clientes as $c)
                                <option value="{{ $c->id }}" @selected(request('cliente_id') == $c->id)>{{ $c->nombre }} - {{ $c->telefono }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block font-semibold mb-1">Colegio</label>
                        <select name="colegio" class="w-full border-gray-300 rounded-md">
                            <option value="">Todos los colegios</option>
                            @foreach ($colegios as $colegio)
                                <option value="{{ $colegio }}" @selected(request('colegio') === $colegio)>{{ $colegio }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block font-semibold mb-1">Estado</label>
                        <select name="estado" class="w-full border-gray-300 rounded-md">
                            <option value="">Todos</option>
                            <option value="pendiente" @selected(request('estado') === 'pendiente')>Pendientes</option>
                            <option value="vencido" @selected(request('estado') === 'vencido')>Vencidos</option>
                            <option value="parcial" @selected(request('estado') === 'parcial')>Devolución parcial</option>
                            <option value="devuelto" @selected(request('estado') === 'devuelto')>Devueltos</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-semibold mb-1">Tipo</label>
                        <select name="tipo" class="w-full border-gray-300 rounded-md">
                            <option value="">Todos</option>
                            <option value="individual" @selected(request('tipo') === 'individual')>Individual</option>
                            <option value="grupal" @selected(request('tipo') === 'grupal')>Curso / grupo</option>
                        </select>
                    </div>
                    <div class="sm:col-span-4 flex flex-wrap gap-2 justify-end">
                        <button type="submit" class="bg-green-700 hover:bg-green-800 text-white px-4 py-2 rounded-md">Consultar</button>
                        <a href="{{ route('reportes.alquileres-periodo') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-md">Limpiar</a>
                        <a href="{{ route('reportes.alquileres-periodo', request()->query() + ['desde' => $desde, 'hasta' => $hasta, 'pdf' => 1]) }}" target="_blank"
                           class="border border-green-700 text-green-700 hover:bg-green-50 px-4 py-2 rounded-md font-semibold">🖨️ PDF</a>
                    </div>
                </form>

                @if ($cliente)
                    <div class="mb-4 p-3 rounded bg-green-50 text-green-900 text-sm">
                        Estado de cuenta de <strong>{{ $cliente->nombre }}</strong> ({{ $cliente->telefono }}) en el período.
                        <a href="{{ route('clientes.show', $cliente->id) }}" class="underline ml-1">Ver historial completo</a>
                    </div>
                @endif

                <!-- Resumen -->
                <div class="grid grid-cols-2 sm:grid-cols-5 gap-3 mb-5">
                    <div class="bg-gray-50 rounded-lg p-3"><p class="text-xs text-gray-500">Alquileres</p><p class="text-xl font-bold">{{ $resumen['alquileres'] }}</p><p class="text-xs text-gray-500">{{ $resumen['grupales'] }} de cursos</p></div>
                    <div class="bg-gray-50 rounded-lg p-3"><p class="text-xs text-gray-500">Trajes alquilados</p><p class="text-xl font-bold">{{ $resumen['unidades'] }}</p></div>
                    <div class="bg-yellow-50 rounded-lg p-3"><p class="text-xs text-gray-500">Pendientes</p><p class="text-xl font-bold text-yellow-700">{{ $resumen['pendientes'] }}</p></div>
                    <div class="bg-red-50 rounded-lg p-3"><p class="text-xs text-gray-500">Vencidos</p><p class="text-xl font-bold text-red-600">{{ $resumen['vencidos'] }}</p></div>
                    <div class="bg-green-50 rounded-lg p-3"><p class="text-xs text-gray-500">Total</p><p class="text-xl font-bold text-green-800">Bs. {{ number_format($resumen['total'], 2) }}</p></div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-green-50 text-green-900">
                            <tr>
                                <th class="p-2 text-left">Recibo</th>
                                <th class="p-2 text-left">Fecha</th>
                                <th class="p-2 text-left">Cliente</th>
                                <th class="p-2 text-left">Tipo</th>
                                <th class="p-2 text-left">Colegio / curso</th>
                                <th class="p-2 text-center">Trajes</th>
                                <th class="p-2 text-left">Pago</th>
                                <th class="p-2 text-right">Total</th>
                                <th class="p-2 text-center">Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($alquileres as $alquiler)
                                <tr class="border-t hover:bg-gray-50">
                                    <td class="p-2 whitespace-nowrap"><a href="{{ route('alquileres.show', $alquiler->id) }}" class="text-green-700 hover:underline">{{ $alquiler->numero_recibo }}</a></td>
                                    <td class="p-2 whitespace-nowrap">{{ $alquiler->fecha_alquiler_formateada }}</td>
                                    <td class="p-2">{{ $alquiler->cliente->nombre }}</td>
                                    <td class="p-2">
                                        <span class="px-2 py-0.5 rounded-full text-xs {{ $alquiler->esGrupal() ? 'bg-purple-100 text-purple-800' : 'bg-gray-100 text-gray-700' }}">
                                            {{ $alquiler->esGrupal() ? 'Curso' : 'Individual' }}
                                        </span>
                                    </td>
                                    <td class="p-2">{{ $alquiler->colegio_direccion ?: '—' }} <span class="text-gray-500">{{ $alquiler->curso }}</span></td>
                                    <td class="p-2 text-center">{{ (int) $alquiler->unidades }}</td>
                                    <td class="p-2">{{ $alquiler->forma_pago }}</td>
                                    <td class="p-2 text-right whitespace-nowrap">Bs. {{ number_format($alquiler->total, 2) }}</td>
                                    <td class="p-2 text-center whitespace-nowrap">
                                        @if ($alquiler->estaVencido())
                                            <span class="px-2 py-0.5 rounded-full text-xs bg-red-100 text-red-800">Vencido</span>
                                        @elseif ($alquiler->estado === 'parcial')
                                            <span class="px-2 py-0.5 rounded-full text-xs bg-orange-100 text-orange-800">Parcial</span>
                                        @elseif ($alquiler->estaPendiente())
                                            <span class="px-2 py-0.5 rounded-full text-xs bg-yellow-100 text-yellow-800">Activo</span>
                                        @else
                                            <span class="px-2 py-0.5 rounded-full text-xs bg-green-100 text-green-800">Devuelto</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="9" class="p-6 text-center text-gray-500">No hay alquileres con esos filtros.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <a href="{{ route('reportes.index') }}" class="inline-block mt-4 text-gray-600">← Volver a Reportes</a>
            </div>
        </div>
    </div>

    <script>
        $(function () {
            $('#filtro-cliente').select2({ placeholder: 'Todos los clientes', allowClear: true, width: '100%' });
        });
    </script>
</x-app-layout>
