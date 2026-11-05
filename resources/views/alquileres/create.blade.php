<x-app-layout>
    {{-- jQuery y Select2 cargados localmente --}}
        <script src="{{ asset('js/jquery.min.js') }}"></script>
        <script src="{{ asset('js/select2.min.js') }}"></script>
    {{-- Select2 CSS cargado localmente --}}
    <link href="{{ asset('css/select2.min.css') }}" rel="stylesheet" />
        <style>
            .select2-container--default .select2-selection--single {
                border: 1px solid #d1d5db;
                border-radius: 0.25rem;
                height: 42px;
                padding: 0.5rem;
            }
            .select2-container--default .select2-selection--single .select2-selection__rendered {
                line-height: 24px;
                color: #374151;
                padding-left: 0;
            }
            .select2-container--default .select2-selection--single .select2-selection__arrow {
                height: 40px;
            }
            /* Opción resaltada al hacer hover/búsqueda — verde */
            .select2-container--default .select2-results__option--highlighted[aria-selected] {
                background-color: #15803d !important;
                color: #fff !important;
            }
            /* Opción ya seleccionada */
            .select2-container--default .select2-results__option[aria-selected=true] {
                background-color: #dcfce7;
                color: #166534;
            }
            /* Borde del input de búsqueda al hacer focus */
            .select2-container--default .select2-search--dropdown .select2-search__field:focus {
                border-color: #16a34a;
                outline: none;
                box-shadow: 0 0 0 2px #bbf7d0;
            }
            /* Selects nativos (garantías, forma de pago) — focus verde */
            select:focus {
                border-color: #16a34a !important;
                outline: none;
                box-shadow: 0 0 0 2px #bbf7d0;
            }
            /* Opción seleccionada en select nativo */
            select option:checked {
                background-color: #15803d;
                color: white;
            }
        </style>
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
                            <div class="flex items-center justify-between mb-1">
                                <label class="block font-semibold">Cliente (Responsable)</label>
                                @can('clientes.crear')
                                    <button type="button" onclick="window.dispatchEvent(new CustomEvent('abrir-nuevo-cliente'))"
                                            class="text-sm text-green-700 hover:text-green-900 font-semibold">
                                        + Nuevo cliente
                                    </button>
                                @endcan
                            </div>
                            <select name="cliente_id" id="select-cliente" class="w-full border rounded p-2">
                                <option value="">-- Buscar cliente --</option>
                                @foreach ($clientes as $cliente)
                                    <option value="{{ $cliente->id }}" @selected(old('cliente_id') == $cliente->id)>{{ $cliente->nombre }} - {{ $cliente->telefono }}</option>
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

                    <button type="submit" class="bg-green-700 hover:bg-green-800 text-white px-6 py-2 rounded-lg font-semibold shadow transition">
                        Guardar Alquiler
                    </button>
                    <a href="{{ route('alquileres.index') }}" class="ml-2 text-gray-600">Cancelar</a>
                </form>

                @can('clientes.crear')
                <!-- Ventana para registrar un cliente nuevo sin salir del alquiler -->
                <div x-data="nuevoCliente()" @abrir-nuevo-cliente.window="abrir()" @keydown.escape.window="abierto = false"
                     x-show="abierto" x-transition.opacity style="display: none;"
                     class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4">
                    <div @click.outside="abierto = false" class="bg-white rounded-lg shadow-xl w-full max-w-md p-6">
                        <h3 class="font-bold text-lg text-green-900 mb-4">Nuevo cliente</h3>

                        <div class="mb-3">
                            <label class="block font-semibold mb-1 text-sm">Nombre</label>
                            <input type="text" x-model="nombre" x-ref="nombre" @keydown.enter.prevent="guardar()"
                                   class="w-full border border-gray-300 rounded-lg p-2 focus:border-green-600 focus:ring-2 focus:ring-green-200 focus:outline-none">
                            <p class="text-red-600 text-sm" x-text="errores.nombre" x-show="errores.nombre"></p>
                        </div>

                        <div class="mb-4">
                            <label class="block font-semibold mb-1 text-sm">Celular</label>
                            <input type="tel" inputmode="numeric" x-model="telefono" @keydown.enter.prevent="guardar()" placeholder="Ej: 71234567"
                                   class="w-full border border-gray-300 rounded-lg p-2 focus:border-green-600 focus:ring-2 focus:ring-green-200 focus:outline-none">
                            <p class="text-gray-500 text-xs mt-1">8 dígitos, empieza con 5, 6 o 7.</p>
                            <p class="text-red-600 text-sm" x-text="errores.telefono" x-show="errores.telefono"></p>
                        </div>

                        <p class="text-red-600 text-sm mb-3" x-text="errores.general" x-show="errores.general"></p>

                        <div class="flex justify-end gap-2">
                            <button type="button" @click="abierto = false" class="px-4 py-2 rounded bg-gray-200 hover:bg-gray-300 text-gray-700">Cancelar</button>
                            <button type="button" @click="guardar()" :disabled="guardando"
                                    class="px-4 py-2 rounded bg-green-700 hover:bg-green-800 disabled:opacity-60 text-white font-semibold">
                                <span x-text="guardando ? 'Guardando...' : 'Guardar y seleccionar'"></span>
                            </button>
                        </div>
                    </div>
                </div>

                <script>
                    function nuevoCliente() {
                        return {
                            abierto: false, guardando: false, nombre: '', telefono: '', errores: {},
                            abrir() {
                                this.nombre = ''; this.telefono = ''; this.errores = {};
                                this.abierto = true;
                                this.$nextTick(() => this.$refs.nombre.focus());
                            },
                            async guardar() {
                                this.guardando = true; this.errores = {};
                                try {
                                    const respuesta = await fetch(@js(route('clientes.store')), {
                                        method: 'POST',
                                        headers: {
                                            'Content-Type': 'application/json',
                                            'Accept': 'application/json',
                                            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                                        },
                                        body: JSON.stringify({ nombre: this.nombre, telefono: this.telefono }),
                                    });
                                    const datos = await respuesta.json();

                                    if (respuesta.status === 422) {
                                        for (const campo in datos.errors) this.errores[campo] = datos.errors[campo][0];
                                        return;
                                    }
                                    if (!respuesta.ok) throw new Error();

                                    // Agrega el cliente al buscador y lo deja seleccionado
                                    const opcion = new Option(datos.nombre + ' - ' + datos.telefono, datos.id, true, true);
                                    $('#select-cliente').append(opcion).trigger('change');
                                    this.abierto = false;
                                } catch (e) {
                                    this.errores.general = 'No se pudo registrar el cliente. Intente de nuevo.';
                                } finally {
                                    this.guardando = false;
                                }
                            },
                        };
                    }
                </script>
                @endcan

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
                            <select name="trajes[${id}][traje_id]" id="select-traje-${id}" class="w-full border rounded p-2 text-sm" onchange="actualizarPrecio(${id}, this)">
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
        setTimeout(() => {
                            $(`#select-traje-${id}`).select2({
                                placeholder: 'Buscar traje...',
                                allowClear: true,
                                width: '100%'
                            });
                        }, 100);
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
                            <select name="garantias[${id}][tipo_garantia]" id="select-garantia-${id}" class="w-full border rounded p-2 text-sm">
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
            setTimeout(() => {
                $(`#select-garantia-${id}`).select2({
                    minimumResultsForSearch: Infinity,
                    width: '100%'
                });
            }, 100);
        }

        // Agregar automáticamente un traje al cargar la página
        agregarTraje();
    </script>
        <script>
            $(document).ready(function() {
                $('#select-cliente').select2({
                    placeholder: 'Escriba nombre o teléfono...',
                    allowClear: true,
                    width: '100%'
                });
            });
    </script>
</x-app-layout>