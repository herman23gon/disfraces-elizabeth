<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Alquiler {{ $alquiler->numero_recibo }}
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

                <h3 class="font-bold text-lg mb-3">Datos del Alquiler</h3>
                <table class="w-full mb-6 text-sm">
                    <tr><td class="font-semibold p-1">Recibo:</td><td>{{ $alquiler->numero_recibo }}</td></tr>
                    <tr><td class="font-semibold p-1">Cliente (Responsable):</td><td>{{ $alquiler->cliente->nombre }} - {{ $alquiler->cliente->telefono }}</td></tr>
                    <tr><td class="font-semibold p-1">Usa el traje:</td><td>{{ $alquiler->nombre_usuario_traje }}</td></tr>
                    <tr><td class="font-semibold p-1">Colegio/Dirección:</td><td>{{ $alquiler->colegio_direccion }}</td></tr>
                    <tr><td class="font-semibold p-1">Curso / Turno:</td><td>{{ $alquiler->curso }} / {{ $alquiler->turno }}</td></tr>
                    <tr><td class="font-semibold p-1">Fecha alquiler:</td><td>{{ $alquiler->fecha_alquiler }}</td></tr>
                    <tr><td class="font-semibold p-1">Fecha devolución:</td><td>{{ $alquiler->fecha_devolucion_programada }}</td></tr>
                    <tr><td class="font-semibold p-1">Estado:</td><td>{{ $alquiler->estado }}</td></tr>
                    <tr><td class="font-semibold p-1">Forma de pago:</td><td>{{ $alquiler->forma_pago }}</td></tr>
                </table>

                <h3 class="font-bold text-lg mb-3">Trajes</h3>
                <table class="w-full mb-6 border text-sm">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="p-2 text-left">Traje</th>
                            <th class="p-2 text-left">Cantidad</th>
                            <th class="p-2 text-left">Piezas</th>
                            <th class="p-2 text-left">Precio Unit.</th>
                            <th class="p-2 text-left">Subtotal</th>
                            <th class="p-2 text-left">Observación</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($alquiler->detalles as $detalle)
                            <tr class="border-t">
                                <td class="p-2">{{ $detalle->traje->nombre }}</td>
                                <td class="p-2">{{ $detalle->cantidad }}</td>
                                <td class="p-2">{{ $detalle->cantidad_piezas }}</td>
                                <td class="p-2">Bs. {{ $detalle->precio_unitario }}</td>
                                <td class="p-2">Bs. {{ $detalle->subtotal }}</td>
                                <td class="p-2">{{ $detalle->estado_entrega }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <h3 class="font-bold text-lg mb-3">Garantías</h3>
                <table class="w-full mb-6 border text-sm">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="p-2 text-left">Tipo</th>
                            <th class="p-2 text-left">Cantidad</th>
                            <th class="p-2 text-left">Monto</th>
                            <th class="p-2 text-left">Descripción</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($alquiler->garantias as $garantia)
                            <tr class="border-t">
                                <td class="p-2">{{ $garantia->tipo_garantia }}</td>
                                <td class="p-2">{{ $garantia->cantidad }}</td>
                                <td class="p-2">{{ $garantia->monto ? 'Bs. '.$garantia->monto : '-' }}</td>
                                <td class="p-2">{{ $garantia->descripcion }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                        <div class="flex justify-end gap-12 mb-6 mt-4">
                    <div class="text-center bg-blue-50 rounded p-4" style="min-width: 200px;">
                        <div class="text-sm text-gray-600 mb-1">TOTAL A PAGAR (Alquiler)</div>
                        <div class="text-2xl font-bold text-blue-700">Bs. {{ number_format($alquiler->total, 2) }}</div>
                    </div>
                    <div class="text-center bg-green-50 rounded p-4" style="min-width: 200px;">
                        <div class="text-sm text-gray-600 mb-1">GARANTÍA RETENIDA</div>
                        <div class="text-sm text-gray-500 mb-1">(a devolver)</div>
                        <div class="text-2xl font-bold text-green-700">
                            Bs. {{ number_format($alquiler->garantias->sum('monto'), 2) }}
                        </div>
                    </div>
                </div>
                <a href="{{ route('alquileres.recibo', $alquiler->id) }}" target="_blank" class="bg-green-600 text-white px-4 py-2 rounded inline-block mb-4">
                    🖨️ Imprimir Recibo
                </a>
                <br>
                <a href="{{ route('alquileres.index') }}" class="text-gray-600">← Volver a la lista</a>
            </div>
        </div>
    </div>
</x-app-layout>