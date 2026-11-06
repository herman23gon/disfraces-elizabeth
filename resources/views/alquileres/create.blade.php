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
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6" x-data="formAlquiler()">

                @if ($errors->any())
                    <div class="mb-4 p-4 bg-red-100 text-red-700 rounded">
                        <p class="font-semibold mb-1">Revise los datos:</p>
                        <ul class="list-disc list-inside text-sm">
                            @foreach (collect($errors->all())->unique() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('alquileres.store') }}" method="POST" id="form-alquiler" @submit="validar($event)">
                    @csrf
                    <input type="hidden" name="tipo" :value="tipo">

                    <!-- 1. Tipo de alquiler -->
                    <div class="flex flex-wrap items-center gap-3 mb-5">
                        <span class="font-semibold">Tipo de alquiler:</span>
                        <div class="inline-flex rounded-lg border border-gray-300 overflow-hidden text-sm">
                            <button type="button" @click="tipo = 'individual'"
                                    :class="tipo === 'individual' ? 'bg-green-700 text-white' : 'bg-white text-gray-700 hover:bg-gray-50'"
                                    class="px-4 py-2 font-semibold">Individual</button>
                            <button type="button" @click="tipo = 'grupal'"
                                    :class="tipo === 'grupal' ? 'bg-green-700 text-white' : 'bg-white text-gray-700 hover:bg-gray-50'"
                                    class="px-4 py-2 font-semibold border-l border-gray-300">Curso / grupo</button>
                        </div>
                        <span class="text-xs text-gray-500" x-show="tipo === 'grupal'">
                            Una tarjeta por persona: quién usa cada traje y qué garantía deja.
                        </span>
                    </div>

                    <h3 class="font-bold text-lg mb-3">Datos del Alquiler</h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label class="block font-semibold" x-text="tipo === 'grupal' ? 'Responsable del grupo' : 'Cliente (Responsable)'"></label>
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
                            <p class="text-xs text-gray-500 mt-1" x-show="tipo === 'grupal'">Papá, profesor o alumno que se hace cargo del grupo.</p>
                        </div>

                        <div x-show="tipo === 'individual'">
                            <label class="block font-semibold mb-1">Nombre de quien usa el traje</label>
                            <input type="text" name="nombre_usuario_traje" value="{{ old('nombre_usuario_traje') }}" class="w-full border rounded p-2">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-4">
                        <div>
                            <label class="block font-semibold mb-1">Colegio / Dirección</label>
                            <input type="text" name="colegio_direccion" list="lista-colegios" value="{{ old('colegio_direccion') }}" class="w-full border rounded p-2" autocomplete="off">
                            <datalist id="lista-colegios">
                                @foreach ($colegios as $colegio)
                                    <option value="{{ $colegio }}">
                                @endforeach
                            </datalist>
                        </div>
                        <div>
                            <label class="block font-semibold mb-1">Curso</label>
                            <input type="text" name="curso" value="{{ old('curso') }}" class="w-full border rounded p-2">
                        </div>
                        <div>
                            <label class="block font-semibold mb-1">Turno</label>
                            <select name="turno" class="w-full border rounded p-2">
                                <option value="">—</option>
                                @foreach (['Mañana', 'Tarde', 'Noche'] as $turno)
                                    <option value="{{ $turno }}" @selected(old('turno') === $turno)>{{ $turno }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
                        <div>
                            <label class="block font-semibold mb-1">Fecha de alquiler</label>
                            <input type="date" name="fecha_alquiler" value="{{ old('fecha_alquiler', date('Y-m-d')) }}" class="w-full border rounded p-2">
                        </div>
                        <div>
                            <label class="block font-semibold mb-1">Devolución programada</label>
                            <input type="date" name="fecha_devolucion_programada" value="{{ old('fecha_devolucion_programada', date('Y-m-d', strtotime('+1 day'))) }}" class="w-full border rounded p-2">
                        </div>
                        <div>
                            <label class="block font-semibold mb-1">Forma de pago</label>
                            <select name="forma_pago" class="w-full border rounded p-2">
                                <option value="Efectivo" @selected(old('forma_pago') === 'Efectivo')>Efectivo</option>
                                <option value="QR" @selected(old('forma_pago') === 'QR')>Pago QR</option>
                            </select>
                        </div>
                    </div>

                    <!-- 2. Trajes (una tarjeta por persona) -->
                    <div class="border-t pt-4 mb-4">
                        <div class="flex flex-wrap justify-between items-center gap-2 mb-3">
                            <h3 class="font-bold text-lg" x-text="tipo === 'grupal' ? 'Trajes por persona' : 'Trajes a alquilar'"></h3>
                            <div class="flex flex-wrap items-center gap-2 text-sm">
                                <template x-if="tipo === 'grupal'">
                                    <div class="flex items-center gap-1">
                                        <span class="text-gray-600">Precio grupal para todos:</span>
                                        <input type="number" step="0.01" min="0" x-model="precioGrupal" class="w-20 border rounded p-1 text-sm">
                                        <button type="button" @click="aplicarPrecioGrupal()" class="border border-green-700 text-green-700 hover:bg-green-50 px-2 py-1 rounded">Aplicar</button>
                                    </div>
                                </template>
                                <button type="button" @click="agregar()" class="bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded font-semibold"
                                        x-text="tipo === 'grupal' ? '+ Agregar persona' : '+ Agregar traje'"></button>
                            </div>
                        </div>

                        <template x-for="(item, i) in items" :key="item.uid">
                            <div class="border rounded-lg mb-2" :class="item.abierto ? 'bg-gray-50 p-3' : 'bg-white px-3 py-2'">

                                <!-- Resumen (tarjeta cerrada) -->
                                <div x-show="!item.abierto" class="flex flex-wrap items-center justify-between gap-2 text-sm">
                                    <button type="button" @click="item.abierto = true" class="text-left flex-1">
                                        <span class="text-gray-400 mr-1" x-text="(i + 1) + '.'"></span>
                                        <span class="font-semibold" x-show="tipo === 'grupal'" x-text="item.persona || 'Sin nombre'"></span>
                                        <span x-show="tipo === 'grupal'"> · </span>
                                        <span x-text="nombreTraje(item) || 'Sin traje'"></span>
                                        <span class="text-gray-500" x-text="' · ' + item.cantidad + ' x Bs ' + Number(item.precio || 0).toFixed(2)"></span>
                                    </button>
                                    <span x-show="tipo === 'grupal'" class="px-2 py-0.5 rounded-full text-xs"
                                          :class="item.garantias.length ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600'"
                                          x-text="item.garantias.length ? item.garantias.map(g => g.tipo_garantia).join(' + ') : 'Sin garantía propia'"></span>
                                    <span class="text-xs text-gray-500 space-x-2">
                                        <button type="button" @click="item.abierto = true" class="text-blue-600 hover:underline">Editar</button>
                                        <button type="button" @click="duplicar(i)" class="text-green-700 hover:underline">Duplicar</button>
                                        <button type="button" @click="quitar(i)" class="text-red-600 hover:underline">Quitar</button>
                                    </span>
                                </div>

                                <!-- Formulario (tarjeta abierta). Los campos siempre existen para enviarse aunque la tarjeta esté cerrada -->
                                <div x-show="item.abierto">
                                    <div class="grid grid-cols-12 gap-2 items-end">
                                        <div class="col-span-12 sm:col-span-4" x-show="tipo === 'grupal'">
                                            <label class="text-sm font-semibold">Persona que usa el traje</label>
                                            <input type="text" :name="`trajes[${i}][persona]`" x-model="item.persona" placeholder="Ej: Juan Pérez" class="w-full border rounded p-2 text-sm">
                                        </div>
                                        <div class="col-span-12" :class="tipo === 'grupal' ? 'sm:col-span-8' : 'sm:col-span-6'">
                                            <label class="text-sm font-semibold">Traje</label>
                                            <select :name="`trajes[${i}][traje_id]`" class="w-full border rounded p-2 text-sm"
                                                    x-init="$nextTick(() => iniciarSelectTraje($el, item))">
                                                <option value="">-- Buscar traje --</option>
                                                @foreach ($trajes as $traje)
                                                    <option value="{{ $traje['id'] }}">{{ $traje['nombre'] }} (Talla {{ $traje['talla'] ?: '—' }}) - Disponibles: {{ $traje['disponible'] }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-span-4 sm:col-span-2">
                                            <label class="text-sm font-semibold">Cantidad</label>
                                            <input type="number" min="1" :name="`trajes[${i}][cantidad]`" x-model.number="item.cantidad" class="w-full border rounded p-2 text-sm">
                                        </div>
                                        <div class="col-span-4 sm:col-span-2">
                                            <label class="text-sm font-semibold">Precio (Bs.)</label>
                                            <input type="number" step="0.01" min="0" :name="`trajes[${i}][precio_unitario]`" x-model.number="item.precio" class="w-full border rounded p-2 text-sm">
                                        </div>
                                        <div class="col-span-4 sm:col-span-2" x-show="!item.piezasTraje.length">
                                            <label class="text-sm font-semibold">Piezas</label>
                                            <input type="number" min="1" x-model.number="item.cantidad_piezas" class="w-full border rounded p-2 text-sm">
                                        </div>
                                        <input type="hidden" :name="`trajes[${i}][cantidad_piezas]`" :value="item.piezasTraje.length ? Math.max(item.piezas.length, 1) : item.cantidad_piezas">
                                    </div>

                                    <!-- Piezas que lleva -->
                                    <div class="mt-2" x-show="item.piezasTraje.length">
                                        <label class="text-sm font-semibold">Piezas que lleva</label>
                                        <div class="flex flex-wrap gap-2 mt-1">
                                            <template x-for="pieza in item.piezasTraje" :key="pieza">
                                                <label class="inline-flex items-center gap-1 text-sm px-2 py-1 rounded border bg-white cursor-pointer">
                                                    <input type="checkbox" :name="`trajes[${i}][piezas][]`" :value="pieza" x-model="item.piezas"
                                                           class="rounded border-gray-300 text-green-600 focus:ring-green-500">
                                                    <span x-text="pieza"></span>
                                                </label>
                                            </template>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-12 gap-2 mt-2">
                                        <div class="col-span-12 sm:col-span-4">
                                            <label class="text-sm font-semibold">Estado al entregar</label>
                                            <select :name="`trajes[${i}][estado_salida]`" x-model="item.estado_salida" class="w-full border rounded p-2 text-sm">
                                                @foreach ($estadosSalida as $estadoSalida)
                                                    <option value="{{ $estadoSalida }}">{{ $estadoSalida }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-span-12 sm:col-span-8">
                                            <label class="text-sm font-semibold">Observación</label>
                                            <input type="text" :name="`trajes[${i}][estado_entrega]`" x-model="item.estado_entrega"
                                                   :placeholder="item.estado_salida === 'Con detalle' ? 'Describa el detalle (ej: manga descosida)' : 'Ej: solo sombrero'"
                                                   class="w-full border rounded p-2 text-sm">
                                        </div>
                                    </div>

                                    <!-- Garantías de esta persona (solo grupal) -->
                                    <div class="mt-3" x-show="tipo === 'grupal'">
                                        <div class="flex items-center justify-between">
                                            <label class="text-sm font-semibold">Garantía de esta persona <span class="font-normal text-gray-500">(opcional)</span></label>
                                            <button type="button" @click="item.garantias.push(nuevaGarantia())" class="text-sm text-green-700 hover:underline">+ Agregar</button>
                                        </div>
                                        <template x-for="(g, j) in item.garantias" :key="j">
                                            <div class="grid grid-cols-12 gap-2 mt-1 items-center">
                                                <select :name="`trajes[${i}][garantias][${j}][tipo_garantia]`" x-model="g.tipo_garantia" class="col-span-4 sm:col-span-3 border rounded p-1.5 text-sm">
                                                    @foreach ($tiposGarantia as $tipoGarantia)
                                                        <option value="{{ $tipoGarantia }}">{{ $tipoGarantia }}</option>
                                                    @endforeach
                                                </select>
                                                <input type="text" :name="`trajes[${i}][garantias][${j}][descripcion]`" x-model="g.descripcion" placeholder="Ej: anillo de oro" class="col-span-8 sm:col-span-5 border rounded p-1.5 text-sm">
                                                <input type="number" step="0.01" min="0" :name="`trajes[${i}][garantias][${j}][monto]`" x-model="g.monto" placeholder="Monto Bs" class="col-span-8 sm:col-span-3 border rounded p-1.5 text-sm">
                                                <button type="button" @click="item.garantias.splice(j, 1)" class="col-span-4 sm:col-span-1 text-red-600 text-sm">Quitar</button>
                                            </div>
                                        </template>
                                        <p x-show="!item.garantias.length" class="text-xs text-gray-500 mt-1">Sin garantía propia (puede quedar cubierta por la garantía del grupo).</p>
                                    </div>

                                    <div class="flex justify-end gap-3 mt-3 text-sm">
                                        <button type="button" @click="duplicar(i)" class="text-green-700 hover:underline" x-show="tipo === 'grupal'">Duplicar para otra persona</button>
                                        <button type="button" @click="quitar(i)" class="text-red-600 hover:underline">Quitar</button>
                                        <button type="button" @click="item.abierto = false" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-3 py-1 rounded" x-show="tipo === 'grupal'">Listo</button>
                                    </div>
                                </div>
                            </div>
                        </template>

                        <p x-show="!items.length" class="text-sm text-gray-500 p-3 border border-dashed rounded">Todavía no se agregó ningún traje.</p>
                    </div>

                    <!-- 3. Garantías generales / del grupo -->
                    <div class="border-t pt-4 mb-6">
                        <div class="flex justify-between items-center mb-1">
                            <h3 class="font-bold text-lg" x-text="tipo === 'grupal' ? 'Garantías del grupo' : 'Garantías entregadas'"></h3>
                            <button type="button" @click="garantias.push(nuevaGarantia())" class="bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded text-sm">
                                + Agregar Garantía
                            </button>
                        </div>
                        <p class="text-xs text-gray-500 mb-2" x-show="tipo === 'grupal'">
                            Opcional. Lo que deja el responsable por todos (ej: 2 celulares, una joya). Se devuelve cuando vuelva el último traje.
                        </p>
                        <template x-for="(g, j) in garantias" :key="j">
                            <div class="grid grid-cols-12 gap-2 mb-2 items-center border rounded p-2 bg-gray-50">
                                <select :name="`garantias[${j}][tipo_garantia]`" x-model="g.tipo_garantia" class="col-span-6 sm:col-span-3 border rounded p-2 text-sm">
                                    @foreach ($tiposGarantia as $tipoGarantia)
                                        <option value="{{ $tipoGarantia }}">{{ $tipoGarantia }}</option>
                                    @endforeach
                                </select>
                                <input type="number" min="1" :name="`garantias[${j}][cantidad]`" x-model="g.cantidad" title="Cantidad" class="col-span-6 sm:col-span-2 border rounded p-2 text-sm">
                                <input type="number" step="0.01" min="0" :name="`garantias[${j}][monto]`" x-model="g.monto" placeholder="Monto Bs" class="col-span-6 sm:col-span-2 border rounded p-2 text-sm">
                                <input type="text" :name="`garantias[${j}][descripcion]`" x-model="g.descripcion" placeholder="Ej: 2 celulares Samsung" class="col-span-6 sm:col-span-4 border rounded p-2 text-sm">
                                <button type="button" @click="garantias.splice(j, 1)" class="col-span-12 sm:col-span-1 text-red-600 text-sm">Quitar</button>
                            </div>
                        </template>
                    </div>

                    <!-- 4. Resumen -->
                    <div class="border-t pt-4 mb-6 flex flex-col items-end gap-1 text-sm">
                        <div x-show="ahorro() > 0" class="text-gray-500">
                            Precio normal: <span class="line-through" x-text="'Bs ' + precioNormal().toFixed(2)"></span>
                            <span class="ml-1 text-green-700" x-text="'(descuento Bs ' + ahorro().toFixed(2) + ')'"></span>
                        </div>
                        <div class="text-lg">
                            <span x-text="unidades() + ' traje(s) · '"></span>
                            Total: <span class="font-bold text-green-800" x-text="'Bs ' + total().toFixed(2)"></span>
                        </div>
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
        // Catálogo de trajes alquilables (id, nombre, talla, precio, piezas)
        const catalogoTrajes = @json($trajes);
        const tipoGarantiaPorDefecto = @json($tiposGarantia[0]);

        function formAlquiler() {
            let siguienteUid = 1;

            // Si el formulario volvió con errores, se reconstruye con lo que se había cargado
            const anteriores = @json(old('trajes', []));
            const garantiasAnteriores = @json(old('garantias', []));

            const crearItem = (datos = {}) => {
                const traje = catalogoTrajes.find(t => t.id == datos.traje_id);
                return {
                    uid: siguienteUid++,
                    abierto: datos.abierto ?? true,
                    persona: datos.persona ?? '',
                    traje_id: datos.traje_id ?? '',
                    cantidad: Number(datos.cantidad ?? 1),
                    precio: datos.precio_unitario ?? datos.precio ?? '',
                    cantidad_piezas: Number(datos.cantidad_piezas ?? 1),
                    piezasTraje: traje ? traje.piezas : [],
                    piezas: datos.piezas ?? (traje ? [...traje.piezas] : []),
                    estado_entrega: datos.estado_entrega ?? '',
                    estado_salida: datos.estado_salida ?? 'Sano',
                    garantias: (datos.garantias ?? []).map(g => ({ ...g })),
                };
            };

            return {
                tipo: @json(old('tipo', 'individual')),
                precioGrupal: '',
                items: Object.values(anteriores).length
                    ? Object.values(anteriores).map(d => crearItem({ ...d, abierto: false }))
                    : [crearItem()],
                garantias: Object.values(garantiasAnteriores).map(g => ({ ...g })),

                nuevaGarantia() {
                    return { tipo_garantia: tipoGarantiaPorDefecto, descripcion: '', cantidad: 1, monto: '' };
                },

                agregar() {
                    this.items.forEach(item => item.abierto = this.tipo !== 'grupal');
                    this.items.push(crearItem());
                },

                // Copia traje, precio, piezas y tipo de garantía; solo hay que cambiar el nombre (y la talla si hace falta)
                duplicar(i) {
                    const original = this.items[i];
                    this.items.forEach(item => item.abierto = false);
                    this.items.splice(i + 1, 0, crearItem({
                        traje_id: original.traje_id,
                        cantidad: original.cantidad,
                        precio: original.precio,
                        cantidad_piezas: original.cantidad_piezas,
                        piezas: [...original.piezas],
                        estado_salida: original.estado_salida,
                        garantias: original.garantias.map(g => ({ ...g, descripcion: '' })),
                    }));
                },

                quitar(i) {
                    this.items.splice(i, 1);
                },

                iniciarSelectTraje(el, item) {
                    $(el).val(item.traje_id).select2({ placeholder: 'Buscar traje...', allowClear: true, width: '100%' })
                        .on('change', () => {
                            item.traje_id = el.value;
                            const traje = catalogoTrajes.find(t => t.id == el.value);
                            item.piezasTraje = traje ? traje.piezas : [];
                            item.piezas = traje ? [...traje.piezas] : [];
                            item.cantidad_piezas = traje ? traje.cantidad_piezas : 1;
                            if (traje) item.precio = (this.tipo === 'grupal' && this.precioGrupal !== '') ? this.precioGrupal : traje.precio;
                        });
                },

                nombreTraje(item) {
                    const traje = catalogoTrajes.find(t => t.id == item.traje_id);
                    return traje ? `${traje.nombre} (${traje.talla || '—'})` : '';
                },

                aplicarPrecioGrupal() {
                    if (this.precioGrupal === '') return;
                    this.items.forEach(item => item.precio = this.precioGrupal);
                },

                unidades() {
                    return this.items.reduce((s, item) => s + Number(item.cantidad || 0), 0);
                },
                total() {
                    return this.items.reduce((s, item) => s + Number(item.cantidad || 0) * Number(item.precio || 0), 0);
                },
                precioNormal() {
                    return this.items.reduce((s, item) => {
                        const traje = catalogoTrajes.find(t => t.id == item.traje_id);
                        return s + Number(item.cantidad || 0) * (traje ? traje.precio : Number(item.precio || 0));
                    }, 0);
                },
                ahorro() {
                    return Math.max(this.precioNormal() - this.total(), 0);
                },

                // Revisión antes de guardar: datos obligatorios y aviso de personas sin garantía
                validar(evento) {
                    const sinTraje = this.items.findIndex(item => !item.traje_id);
                    if (!this.items.length || sinTraje !== -1) {
                        evento.preventDefault();
                        if (sinTraje !== -1) this.items[sinTraje].abierto = true;
                        alert('Seleccione el traje en todas las tarjetas.');
                        return;
                    }

                    if (this.tipo !== 'grupal') return;

                    const sinNombre = this.items.findIndex(item => !item.persona.trim());
                    if (sinNombre !== -1) {
                        evento.preventDefault();
                        this.items[sinNombre].abierto = true;
                        alert('Escriba el nombre de la persona en todas las tarjetas.');
                        return;
                    }

                    const sinGarantia = this.items.filter(item => !item.garantias.length).map(item => item.persona);
                    if (sinGarantia.length && !this.garantias.length &&
                        !confirm(`Sin garantía: ${sinGarantia.join(', ')}.\nTampoco hay garantía del grupo. ¿Guardar de todos modos?`)) {
                        evento.preventDefault();
                    }
                },
            };
        }
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
