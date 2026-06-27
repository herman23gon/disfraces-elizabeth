<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Registrar Devolución - {{ $alquiler->numero_recibo }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">

                <h3 class="font-bold text-lg mb-3">Resumen del Alquiler</h3>
                <table class="w-full mb-6 text-sm border">
                    <tr><td class="font-semibold p-2">Cliente:</td><td class="p-2">{{ $alquiler->cliente->nombre }}</td></tr>
                    <tr><td class="font-semibold p-2">Fecha alquiler:</td><td class="p-2">{{ $alquiler->fecha_alquiler }}</td></tr>
                    <tr><td class="font-semibold p-2">Fecha devolución programada:</td><td class="p-2">{{ $alquiler->fecha_devolucion_programada }}</td></tr>
                </table>

                <h3 class="font-bold text-lg mb-3">Trajes a devolver</h3>
                <table class="w-full mb-6 border text-sm">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="p-2 text-left">Traje</th>
                            <th class="p-2 text-left">Cantidad alquilada</th>
                            <th class="p-2 text-left">Piezas</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($alquiler->detalles as $detalle)
                            <tr class="border-t">
                                <td class="p-2">{{ $detalle->traje->nombre }}</td>
                                <td class="p-2">{{ $detalle->cantidad }}</td>
                                <td class="p-2">{{ $detalle->cantidad_piezas }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <h3 class="font-bold text-lg mb-3">Garantías a devolver</h3>
                <table class="w-full mb-6 border text-sm">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="p-2 text-left">Tipo</th>
                            <th class="p-2 text-left">Cantidad</th>
                            <th class="p-2 text-left">Monto</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($alquiler->garantias as $garantia)
                            <tr class="border-t">
                                <td class="p-2">{{ $garantia->tipo_garantia }}</td>
                                <td class="p-2">{{ $garantia->cantidad }}</td>
                                <td class="p-2">{{ $garantia->monto ? 'Bs. '.$garantia->monto : '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <form action="{{ route('devoluciones.store', $alquiler->id) }}" method="POST">
                    @csrf

                    <div class="mb-4">
                        <label class="block font-semibold mb-1">Fecha de devolución</label>
                        <input type="date" name="fecha_devolucion" value="{{ date('Y-m-d') }}" class="w-full border rounded p-2">
                        @error('fecha_devolucion')
                            <p class="text-red-600 text-sm">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label class="block font-semibold mb-1">Observaciones (ej: daño, atraso)</label>
                        <textarea name="observaciones" class="w-full border rounded p-2"></textarea>
                    </div>

                    <div class="mb-4">
                        <label class="block font-semibold mb-1">Penalidad a descontar (Bs.)</label>
                        <input type="number" step="0.01" name="penalidad" value="0" class="w-full border rounded p-2">
                        @error('penalidad')
                            <p class="text-red-600 text-sm">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="mb-4">
                        <label class="block font-semibold mb-1">Forma de pago de la penalidad (solo si aplica)</label>
                        <select name="forma_pago_penalidad" class="w-full border rounded p-2">
                            <option value="">-- No aplica --</option>
                            <option value="Efectivo">Efectivo</option>
                            <option value="QR">Pago QR</option>
                        </select>
                        @error('forma_pago_penalidad')
                            <p class="text-red-600 text-sm">{{ $message }}</p>
                        @enderror
                    </div>
                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">
                        Confirmar Devolución
                    </button>
                    <a href="{{ route('alquileres.show', $alquiler->id) }}" class="ml-2 text-gray-600">Cancelar</a>
                </form>

            </div>
        </div>
    </div>
</x-app-layout>