<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Nuevo Alquiler
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">

                <form action="{{ route('alquileres.store') }}" method="POST" id="form-alquiler">
                    @csrf

                    <h3 class="font-bold text-lg mb-3">Datos del Alquiler</h3>

                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block font-semibold mb-1">Cliente (Responsable)</label>
                            <select name="cliente_id" class="w-full border rounded p-2">
                                <option value="">-- Seleccione --</option>
                                @foreach ($clientes as $cliente)
                                    <option value="{{ $cliente->id }}">{{ $cliente->nombre }} - {{ $cliente->telefono }}</option>
                                @endforeach
                            </select>
                            @error('cliente_id')
                                <p class="text-red-600 text-sm">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block font-semibold mb-1">Nombre de quien usa el traje</label>
                            <input type="text" name="nombre_usuario_traje" class="w-full border rounded p-2">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block font-semibold mb-1">Colegio / Dirección</label>
                            <input type="text" name="colegio_direccion" class="w-full border rounded p-2">
                        </div>
                        <div>
                            <label class="block font-semibold mb-1">Estado del traje (al entregar)</label>
                            <input type="text" name="estado_traje_entrega" placeholder="Ej: Sano" class="w-full border rounded p-2">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block font-semibold mb-1">Curso</label>
                            <input type="text" name="curso" class="w-full border rounded p-2">
                        </div>
                        <div>
                            <label class="block font-semibold mb-1">Turno</label>
                            <input type="text" name="turno" class="w-full border rounded p-2">
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="block font-semibold mb-1">Forma de pago (del alquiler)</label>
                        <select name="forma_pago" class="w-full border rounded p-2">
                            <option value="Efectivo">Efectivo</option>
                            <option value="QR">Pago QR</option>
                        </select>
                        @error('forma_pago')
                            <p class="text-red-600 text-sm">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-4 mb-6">
                        <div>
                            <label class="block font-semibold mb-1">Fecha de alquiler</label>
                            <input type="date" name="fecha_alquiler" value="{{ date('Y-m-d') }}" class="w-full border rounded p-2">
                            @error('fecha_alquiler')
                                <p class="text-red-600 text-sm">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block font-semibold mb-1">Fecha de devolución programada</label>
                            <input type="date" name="fecha_devolucion_programada" value="{{ date('Y-m-d', strtotime('+1 day')) }}" class="w-full border rounded p-2">
                            @error('fecha_devolucion_programada')
                                <p class="text-red-600 text-sm">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="border-t pt-4 mb-4">
                        <div class="flex justify-between items-center mb-3">
                            <h3 class="font-bold text-lg">Trajes a alquilar</h3>
                            <button type="button" onclick="agregarTraje()" class="bg-green-600 text-white px-3 py-1 rounded text-sm">
                                + Agregar Traje
                            </button>
                        </div>

                        <div id="contenedor-trajes"></div>
                        @error('trajes')
                            <p class="text-red-600 text-sm">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="border-t pt-4 mb-6">
                        <div class="flex justify-between items-center mb-3">
                            <h3 class="font-bold text-lg">Garantías entregadas</h3>
                            <button type="button" onclick="agregarGarantia()" class="bg-green-600 text-white px-3 py-1 rounded text-sm">
                                + Agregar Garantía
                            </button>
                        </div>

                        <div id="contenedor-garantias"></div>
                    </div>

                    <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded font-semibold">
                        Guardar Alquiler
                    </button>
                    <a href="{{ route('alquileres.index') }}" class="ml-2 text-gray-600">Cancelar</a>
                </form>

            </div>
        </div>
    </div>
    <script>
        let contadorTrajes = 0;
        let contadorGarantias = 0;

        const trajesDisponibles = @json($trajes);

        function agregarTraje() {
            const id = contadorTrajes++;
            const opciones = trajesDisponibles.map(t =>
                `<option value="${t.id}" data-precio="${t.precio_referencia}" data-piezas="${t.cantidad_piezas}">${t.nombre} (Talla ${t.talla}) - Disponibles: ${t.cantidad_disponible}</option>`
            ).join('');

            const html = `
                <div class="border rounded p-3 mb-2 bg-gray-50" id="traje-${id}">
                    <div class="grid grid-cols-12 gap-2 items-end">
                        <div class="col-span-4">
                            <label class="text-sm font-semibold">Traje</label>
                            <select name="trajes[${id}][traje_id]" class="w-full border rounded p-2 text-sm" onchange="actualizarPrecio(${id}, this)">
                                <option value="">-- Seleccione --</option>
                                ${opciones}
                            </select>
                        </div>
                        <div class="col-span-2">
                            <label class="text-sm font-semibold">Cantidad</label>
                            <input type="number" name="trajes[${id}][cantidad]" value="1" min="1" class="w-full border rounded p-2 text-sm">
                        </div>
                        <div class="col-span-2">
                            <label class="text-sm font-semibold">Piezas</label>
                            <input type="number" name="trajes[${id}][cantidad_piezas]" value="1" min="1" class="w-full border rounded p-2 text-sm" id="piezas-${id}">
                        </div>
                        <div class="col-span-2">
                            <label class="text-sm font-semibold">Precio (Bs.)</label>
                            <input type="number" step="0.01" name="trajes[${id}][precio_unitario]" value="0" class="w-full border rounded p-2 text-sm" id="precio-${id}">
                        </div>
                        <div class="col-span-2">
                            <button type="button" onclick="document.getElementById('traje-${id}').remove()" class="text-red-600 text-sm">Quitar</button>
                        </div>
                    </div>
                    <div class="mt-2">
                        <label class="text-sm font-semibold">Observación (ej: "Solo sombrero")</label>
                        <input type="text" name="trajes[${id}][estado_entrega]" class="w-full border rounded p-2 text-sm">
                    </div>
                </div>
            `;
            document.getElementById('contenedor-trajes').insertAdjacentHTML('beforeend', html);
        }

        function actualizarPrecio(id, select) {
            const opcionSeleccionada = select.options[select.selectedIndex];
            const precio = opcionSeleccionada.getAttribute('data-precio') || 0;
            const piezas = opcionSeleccionada.getAttribute('data-piezas') || 1;
            document.getElementById('precio-' + id).value = precio;
            document.getElementById('piezas-' + id).value = piezas;
        }

        function agregarGarantia() {
            const id = contadorGarantias++;
            const html = `
                <div class="border rounded p-3 mb-2 bg-gray-50" id="garantia-${id}">
                    <div class="grid grid-cols-12 gap-2 items-end">
                        <div class="col-span-3">
                            <label class="text-sm font-semibold">Tipo</label>
                            <select name="garantias[${id}][tipo_garantia]" class="w-full border rounded p-2 text-sm">
                                <option value="Efectivo">Efectivo</option>
                                <option value="Pago QR">Pago QR</option>
                                <option value="Celular">Celular</option>
                                <option value="Joya">Joya</option>
                                <option value="Carnet">Carnet de identidad</option>
                            </select>
                        </div>
                        <div class="col-span-2">
                            <label class="text-sm font-semibold">Cantidad</label>
                            <input type="number" name="garantias[${id}][cantidad]" value="1" min="1" class="w-full border rounded p-2 text-sm">
                        </div>
                        <div class="col-span-2">
                            <label class="text-sm font-semibold">Monto (Bs.)</label>
                            <input type="number" step="0.01" name="garantias[${id}][monto]" class="w-full border rounded p-2 text-sm">
                        </div>
                        <div class="col-span-4">
                            <label class="text-sm font-semibold">Descripción</label>
                            <input type="text" name="garantias[${id}][descripcion]" placeholder="Ej: 2 celulares Samsung" class="w-full border rounded p-2 text-sm">
                        </div>
                        <div class="col-span-1">
                            <button type="button" onclick="document.getElementById('garantia-${id}').remove()" class="text-red-600 text-sm">X</button>
                        </div>
                    </div>
                </div>
            `;
            document.getElementById('contenedor-garantias').insertAdjacentHTML('beforeend', html);
        }

        // Agregar automáticamente un traje al cargar la página
        agregarTraje();
    </script>
</x-app-layout>