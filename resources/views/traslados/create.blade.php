<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuevo Traslado - Sistema de Importaciones</title>
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50">
    <x-sidebar :role="auth()->user()->role->nombre" />

    <div class="md:ml-64 p-4 md:p-8">
        <x-header
            title="Nuevo Traslado"
            subtitle="Registra un traslado de stock entre almacenes o tiendas"
        />

        {{-- Navegación rápida --}}
        <div class="flex flex-wrap gap-3 mb-6">
            <a href="{{ route('traslados.index') }}"
               class="text-sm text-gray-600 hover:text-blue-700 flex items-center gap-1">
                <i class="fas fa-exchange-alt"></i> Historial
            </a>
            <span class="text-gray-300">|</span>
            <a href="{{ route('traslados.pendientes') }}"
               class="text-sm text-gray-600 hover:text-yellow-600 flex items-center gap-1">
                <i class="fas fa-clock"></i> Pendientes
            </a>
            <span class="text-gray-300">|</span>
            <a href="{{ route('traslados.stock') }}"
               class="text-sm text-gray-600 hover:text-blue-700 flex items-center gap-1">
                <i class="fas fa-boxes"></i> Stock por Almacén
            </a>
            <span class="text-gray-300">|</span>
            <span class="text-sm font-semibold text-blue-700 flex items-center gap-1">
                <i class="fas fa-plus-circle"></i> Nuevo Traslado
            </span>
        </div>

        @if(session('error'))
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-lg flex items-center gap-2">
                <i class="fas fa-exclamation-circle"></i>{{ session('error') }}
            </div>
        @endif

        <div class="max-w-2xl mx-auto"
             x-data="trasladoForm()"
             x-init="init()">

            {{-- Card --}}
            <div class="bg-white rounded-2xl shadow-md overflow-hidden">

                {{-- Header --}}
                <div class="bg-linear-to-r from-blue-900 to-blue-700 px-6 py-5 flex items-center gap-3">
                    <div class="bg-white/20 rounded-xl p-2.5">
                        <i class="fas fa-exchange-alt text-white text-xl"></i>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-white">Registrar Traslado</h2>
                        <p class="text-blue-200 text-sm">El stock se descuenta del origen al crear</p>
                    </div>
                </div>

                <form action="{{ route('traslados.store') }}" method="POST" class="p-6 space-y-5">
                    @csrf

                    {{-- Producto --}}
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">
                            <i class="fas fa-box mr-1 text-blue-500"></i>Producto *
                        </label>
                        <select name="producto_id"
                                required
                                x-model="productoId"
                                @change="onProductoChange()"
                                class="w-full px-3 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white">
                            <option value="">— Seleccione un producto —</option>
                            @foreach($productos as $prod)
                                <option value="{{ $prod->id }}"
                                        data-tipo="{{ $prod->tipo_inventario }}"
                                        {{ (old('producto_id', $selectedProductoId) == $prod->id) ? 'selected' : '' }}>
                                    {{ $prod->nombre }}
                                    @if($prod->tipo_inventario === 'serie')
                                        (IMEI)
                                    @endif
                                    — {{ $prod->codigo }}
                                </option>
                            @endforeach
                        </select>
                        @error('producto_id')
                            <p class="text-red-500 text-xs mt-1"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Badge producto serie --}}
                    <div x-show="esSerie" x-cloak
                         class="flex items-start gap-2 bg-purple-50 border border-purple-200 rounded-lg px-4 py-3 text-sm text-purple-800">
                        <i class="fas fa-barcode mt-0.5 text-purple-500 shrink-0"></i>
                        <span><strong>Producto rastreado por IMEI.</strong> Selecciona los IMEIs específicos que deseas trasladar.</span>
                    </div>

                    {{-- Origen / Destino --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">
                                <i class="fas fa-warehouse mr-1 text-orange-500"></i>Almacén Origen *
                            </label>
                            <select name="almacen_id"
                                    required
                                    x-model="almacenId"
                                    @change="onAlmacenChange()"
                                    class="w-full px-3 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white">
                                <option value="">— Seleccione origen —</option>
                                @foreach($almacenes as $alm)
                                    <option value="{{ $alm->id }}" {{ old('almacen_id') == $alm->id ? 'selected' : '' }}>
                                        {{ $alm->nombre }}
                                    </option>
                                @endforeach
                            </select>
                            @error('almacen_id')
                                <p class="text-red-500 text-xs mt-1"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
                            @enderror

                            {{-- Stock disponible en origen --}}
                            <div x-show="stockOrigen !== null" x-cloak class="mt-1.5">
                                <span class="text-xs"
                                      :class="stockOrigen > 0 ? 'text-green-600' : 'text-red-500'">
                                    <i class="fas fa-cubes mr-1"></i>
                                    Disponibles:
                                    <strong x-text="stockOrigen"></strong>
                                    <span x-show="esSerie" x-cloak class="text-purple-600"> IMEIs</span>
                                    <span x-show="!esSerie" x-cloak> unidades</span>
                                </span>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">
                                <i class="fas fa-store mr-1 text-green-500"></i>Almacén Destino *
                            </label>
                            <select name="almacen_destino_id"
                                    required
                                    x-model="almacenDestinoId"
                                    class="w-full px-3 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white">
                                <option value="">— Seleccione destino —</option>
                                @foreach($almacenes as $alm)
                                    <option value="{{ $alm->id }}" {{ old('almacen_destino_id') == $alm->id ? 'selected' : '' }}>
                                        {{ $alm->nombre }}
                                    </option>
                                @endforeach
                            </select>
                            @error('almacen_destino_id')
                                <p class="text-red-500 text-xs mt-1"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
                            @enderror

                            {{-- Advertencia mismo almacén --}}
                            <div x-show="almacenId && almacenDestinoId && almacenId === almacenDestinoId" x-cloak
                                 class="mt-1.5 text-xs text-red-500">
                                <i class="fas fa-exclamation-triangle mr-1"></i>Origen y destino no pueden ser iguales
                            </div>
                        </div>
                    </div>

                    {{-- ═══ CANTIDAD (solo productos sin IMEI) ═══ --}}
                    <div x-show="!esSerie">
                        <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">
                            <i class="fas fa-hashtag mr-1 text-blue-500"></i>Cantidad *
                        </label>
                        <input type="number"
                               name="cantidad"
                               min="1"
                               x-model="cantidad"
                               :required="!esSerie"
                               :max="stockOrigen ?? undefined"
                               class="w-full px-3 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               value="{{ old('cantidad', 1) }}">
                        @error('cantidad')
                            <p class="text-red-500 text-xs mt-1"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
                        @enderror
                        <div x-show="stockOrigen !== null && stockOrigen < cantidad && cantidad > 0" x-cloak
                             class="mt-1.5 text-xs text-red-500">
                            <i class="fas fa-exclamation-triangle mr-1"></i>La cantidad supera el stock disponible en origen
                        </div>
                    </div>

                    {{-- ═══ SELECTOR DE IMEIs (solo productos serie) ═══ --}}
                    <div x-show="esSerie" x-cloak>
                        <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">
                            <i class="fas fa-barcode mr-1 text-purple-500"></i>IMEIs a trasladar *
                        </label>

                        {{-- Mensaje cuando no hay producto o almacén seleccionado --}}
                        <div x-show="!productoId || !almacenId"
                             class="bg-gray-50 border border-dashed border-gray-300 rounded-lg px-4 py-5 text-center text-sm text-gray-400">
                            <i class="fas fa-arrow-up block text-lg mb-1 text-gray-300"></i>
                            Seleccione producto y almacén origen para ver los IMEIs disponibles
                        </div>

                        {{-- Panel de IMEIs --}}
                        <div x-show="productoId && almacenId" x-cloak>

                            {{-- Barra de búsqueda + contador --}}
                            <div class="flex items-center gap-2 mb-2">
                                <div class="relative flex-1">
                                    <i class="fas fa-search absolute left-2.5 top-2.5 text-xs text-gray-400 pointer-events-none"></i>
                                    <input type="text"
                                           x-model="imeiBusqueda"
                                           placeholder="Buscar por IMEI o serie..."
                                           class="w-full pl-8 pr-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-400 focus:border-purple-400">
                                </div>
                                <div class="text-xs text-gray-500 shrink-0 bg-gray-100 rounded-lg px-3 py-2 font-mono">
                                    <span x-text="imeisSeleccionados.length" class="font-bold text-purple-700"></span>
                                    /
                                    <span x-text="imeisDisponibles.length"></span>
                                </div>
                            </div>

                            {{-- Loading --}}
                            <div x-show="imeisLoading" class="py-6 text-center text-sm text-gray-400">
                                <i class="fas fa-spinner fa-spin mr-2"></i>Cargando IMEIs...
                            </div>

                            {{-- Sin resultados --}}
                            <div x-show="!imeisLoading && imeisDisponibles.length === 0" x-cloak
                                 class="py-6 text-center text-sm text-gray-400 bg-gray-50 border border-dashed border-gray-200 rounded-lg">
                                <i class="fas fa-box-open text-2xl text-gray-300 block mb-2"></i>
                                No hay IMEIs disponibles en este almacén para el producto seleccionado
                            </div>

                            {{-- Lista de IMEIs --}}
                            <div x-show="!imeisLoading && imeisDisponibles.length > 0" x-cloak
                                 class="max-h-56 overflow-y-auto border border-gray-200 rounded-lg divide-y divide-gray-100">

                                {{-- Seleccionar todos --}}
                                <div class="flex items-center justify-between px-3 py-2 bg-gray-50 sticky top-0 z-10 border-b border-gray-200">
                                    <button type="button"
                                            @click="seleccionarTodos()"
                                            class="text-xs text-purple-600 hover:text-purple-800 font-semibold flex items-center gap-1">
                                        <i class="fas fa-check-double"></i>
                                        <span x-text="imeisSeleccionados.length === imeisFiltrados.length && imeisFiltrados.length > 0 ? 'Deseleccionar todos' : 'Seleccionar todos'"></span>
                                    </button>
                                    <span class="text-xs text-gray-400" x-show="imeiBusqueda" x-cloak>
                                        <span x-text="imeisFiltrados.length"></span> resultado(s)
                                    </span>
                                </div>

                                <template x-for="imei in imeisFiltrados" :key="imei.id">
                                    <label :for="'imei-' + imei.id"
                                           class="flex items-center gap-3 px-3 py-2.5 cursor-pointer transition-colors"
                                           :class="isSelected(imei.id) ? 'bg-purple-50 hover:bg-purple-100' : 'hover:bg-gray-50'">
                                        <input type="checkbox"
                                               :id="'imei-' + imei.id"
                                               :value="imei.id"
                                               :checked="isSelected(imei.id)"
                                               @change="toggleImei(imei.id)"
                                               class="w-4 h-4 accent-purple-600 cursor-pointer shrink-0">
                                        <div class="flex-1 min-w-0">
                                            <span class="font-mono text-sm font-semibold text-gray-800"
                                                  :class="isSelected(imei.id) ? 'text-purple-700' : ''"
                                                  x-text="imei.codigo_imei"></span>
                                            <span x-show="imei.serie"
                                                  class="text-xs text-gray-400 ml-2 font-mono"
                                                  x-text="'S/N: ' + imei.serie"></span>
                                        </div>
                                        <span x-show="isSelected(imei.id)"
                                              class="text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded-full font-semibold shrink-0">
                                            ✓
                                        </span>
                                        <span x-show="!isSelected(imei.id)"
                                              class="text-xs bg-green-100 text-green-700 px-2 py-0.5 rounded-full shrink-0">
                                            En stock
                                        </span>
                                    </label>
                                </template>

                                {{-- Sin resultados de búsqueda --}}
                                <div x-show="imeiBusqueda && imeisFiltrados.length === 0" x-cloak
                                     class="px-3 py-4 text-center text-xs text-gray-400">
                                    Sin resultados para "<span x-text="imeiBusqueda"></span>"
                                </div>
                            </div>

                            {{-- Resumen selección --}}
                            <div x-show="imeisSeleccionados.length > 0" x-cloak
                                 class="mt-2 flex flex-wrap gap-1.5">
                                <template x-for="id in imeisSeleccionados" :key="id">
                                    <span class="inline-flex items-center gap-1 bg-purple-100 text-purple-800 text-xs font-mono px-2 py-1 rounded-lg border border-purple-200">
                                        <span x-text="getImeiCodigo(id)"></span>
                                        <button type="button" @click="toggleImei(id)"
                                                class="text-purple-400 hover:text-red-500 transition ml-0.5">
                                            <i class="fas fa-times text-[10px]"></i>
                                        </button>
                                    </span>
                                </template>
                            </div>

                            {{-- Error de validación --}}
                            @error('imei_ids')
                                <p class="text-red-500 text-xs mt-1"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
                            @enderror

                            {{-- Hidden inputs para los IMEIs seleccionados --}}
                            <template x-for="id in imeisSeleccionados" :key="id">
                                <input type="hidden" name="imei_ids[]" :value="id">
                            </template>
                        </div>
                    </div>

                    {{-- Número de Guía --}}
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">
                            <i class="fas fa-file-alt mr-1 text-blue-400"></i>Número de Guía
                            <span class="text-gray-400 font-normal normal-case">(opcional — se auto-genera si se deja vacío)</span>
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-3 flex items-center text-gray-400 text-sm font-mono pointer-events-none">GR-</span>
                            <input type="text"
                                   name="numero_guia"
                                   class="w-full pl-10 pr-3 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 font-mono uppercase"
                                   placeholder="Ej: GR-00042 o TRS-2024-001"
                                   value="{{ old('numero_guia') }}"
                                   oninput="this.value = this.value.toUpperCase()">
                        </div>
                        @error('numero_guia')
                            <p class="text-red-500 text-xs mt-1"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
                        @enderror
                        <p class="text-xs text-gray-400 mt-1">
                            <i class="fas fa-magic mr-1"></i>Si no ingresas uno, el sistema generará automáticamente el siguiente número correlativo.
                        </p>
                    </div>

                    {{-- Transportista --}}
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">
                            <i class="fas fa-truck mr-1 text-gray-400"></i>Transportista <span class="text-gray-400 font-normal normal-case">(opcional)</span>
                        </label>
                        <input type="text"
                               name="transportista"
                               class="w-full px-3 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               placeholder="Nombre del transportista o empresa"
                               value="{{ old('transportista') }}">
                    </div>

                    {{-- Observaciones --}}
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">
                            <i class="fas fa-comment-alt mr-1 text-gray-400"></i>Observaciones <span class="text-gray-400 font-normal normal-case">(opcional)</span>
                        </label>
                        <textarea name="observaciones"
                                  rows="2"
                                  class="w-full px-3 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 resize-none"
                                  placeholder="Motivo del traslado, instrucciones especiales...">{{ old('observaciones') }}</textarea>
                    </div>

                    {{-- Info box --}}
                    <div class="bg-blue-50 border border-blue-200 rounded-lg px-4 py-3 flex gap-2 text-sm text-blue-800">
                        <i class="fas fa-info-circle mt-0.5 text-blue-500 shrink-0"></i>
                        <span>
                            Para productos con IMEI: los equipos quedan asignados al traslado y se moverán al destino al <strong>confirmar la recepción</strong>.
                            Para accesorios: el stock se descuenta del origen al registrar.
                        </span>
                    </div>

                    {{-- Acciones --}}
                    <div class="flex justify-end gap-3 pt-2 border-t border-gray-100">
                        <a href="{{ route('traslados.index') }}"
                           class="px-5 py-2.5 border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition-colors">
                            Cancelar
                        </a>
                        <button type="submit"
                                :disabled="!puedeEnviar()"
                                class="px-6 py-2.5 bg-blue-700 hover:bg-blue-800 text-white text-sm font-semibold rounded-lg flex items-center gap-2 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                            <i class="fas fa-paper-plane"></i>
                            <span x-text="esSerie ? 'Enviar ' + imeisSeleccionados.length + ' IMEI(s)' : 'Enviar Traslado'"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

<script>
function trasladoForm() {
    const stocks = @json($stocksData->toArray());
    const imeis  = @json($imeisData->toArray());
    const tipos  = @json($tiposInventario->toArray());

    return {
        productoId:        '{{ old('producto_id', $selectedProductoId ?? '') }}',
        almacenId:         '{{ old('almacen_id', '') }}',
        almacenDestinoId:  '{{ old('almacen_destino_id', '') }}',
        cantidad:           {{ old('cantidad', 1) }},
        esSerie:           false,
        stockOrigen:       null,

        // IMEI
        imeisDisponibles:  [],
        imeisSeleccionados: [],
        imeiBusqueda:      '',
        imeisLoading:      false,

        get imeisFiltrados() {
            if (!this.imeiBusqueda.trim()) return this.imeisDisponibles;
            const q = this.imeiBusqueda.toLowerCase();
            return this.imeisDisponibles.filter(i =>
                i.codigo_imei.toLowerCase().includes(q) ||
                (i.serie && i.serie.toLowerCase().includes(q))
            );
        },

        init() {
            this.onProductoChange();
        },

        onProductoChange() {
            const pid    = String(this.productoId);
            this.esSerie = pid && tipos[pid] === 'serie';
            this.imeisSeleccionados = [];
            this.imeisDisponibles   = [];
            this.imeiBusqueda       = '';
            this.calcularStock();
            if (this.esSerie && this.almacenId) {
                this.cargarImeis();
            }
        },

        onAlmacenChange() {
            this.imeisSeleccionados = [];
            this.imeisDisponibles   = [];
            this.imeiBusqueda       = '';
            this.calcularStock();
            if (this.esSerie && this.productoId && this.almacenId) {
                this.cargarImeis();
            }
        },

        async cargarImeis() {
            this.imeisLoading = true;
            try {
                const url  = `{{ route('traslados.imeis-disponibles') }}?producto_id=${this.productoId}&almacen_id=${this.almacenId}`;
                const resp = await fetch(url, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                this.imeisDisponibles = await resp.json();
            } catch (e) {
                this.imeisDisponibles = [];
            } finally {
                this.imeisLoading = false;
            }
        },

        toggleImei(id) {
            const idx = this.imeisSeleccionados.indexOf(id);
            if (idx === -1) {
                this.imeisSeleccionados.push(id);
            } else {
                this.imeisSeleccionados.splice(idx, 1);
            }
        },

        isSelected(id) {
            return this.imeisSeleccionados.includes(id);
        },

        seleccionarTodos() {
            const filtradosIds = this.imeisFiltrados.map(i => i.id);
            const todosSelected = filtradosIds.every(id => this.imeisSeleccionados.includes(id));
            if (todosSelected) {
                this.imeisSeleccionados = this.imeisSeleccionados.filter(id => !filtradosIds.includes(id));
            } else {
                filtradosIds.forEach(id => {
                    if (!this.imeisSeleccionados.includes(id)) {
                        this.imeisSeleccionados.push(id);
                    }
                });
            }
        },

        getImeiCodigo(id) {
            const imei = this.imeisDisponibles.find(i => i.id === id);
            return imei ? imei.codigo_imei : id;
        },

        calcularStock() {
            const pid = String(this.productoId);
            const aid = String(this.almacenId);

            if (!pid || !aid) {
                this.stockOrigen = null;
                return;
            }

            if (this.esSerie) {
                this.stockOrigen = (imeis[pid] && imeis[pid][aid] !== undefined)
                    ? parseInt(imeis[pid][aid])
                    : 0;
            } else {
                this.stockOrigen = (stocks[pid] && stocks[pid][aid] !== undefined)
                    ? parseInt(stocks[pid][aid])
                    : 0;
            }
        },

        puedeEnviar() {
            if (this.almacenId && this.almacenDestinoId && this.almacenId === this.almacenDestinoId) return false;
            if (this.esSerie) return this.imeisSeleccionados.length > 0;
            return this.cantidad > 0 && (this.stockOrigen === null || this.stockOrigen >= this.cantidad);
        },
    };
}
</script>
</body>
</html>
