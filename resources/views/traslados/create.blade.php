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
                        <p class="text-blue-200 text-sm">El stock se desconta del origen al crear</p>
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
                        <i class="fas fa-barcode mt-0.5 text-purple-500"></i>
                        <span><strong>Producto rastreado por IMEI.</strong> Deberás seleccionar los IMEIs específicos al confirmar la recepción.</span>
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
                                    Stock disponible:
                                    <strong x-text="stockOrigen"></strong>
                                    <span x-show="esSerie" x-cloak class="text-purple-600">(IMEIs)</span>
                                    <span x-show="!esSerie" x-cloak>unidades</span>
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

                    {{-- Cantidad --}}
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">
                            <i class="fas fa-hashtag mr-1 text-blue-500"></i>Cantidad *
                        </label>
                        <input type="number"
                               name="cantidad"
                               min="1"
                               required
                               x-model="cantidad"
                               :max="stockOrigen ?? undefined"
                               class="w-full px-3 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               value="{{ old('cantidad', 1) }}">
                        @error('cantidad')
                            <p class="text-red-500 text-xs mt-1"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
                        @enderror

                        {{-- Alerta stock insuficiente --}}
                        <div x-show="stockOrigen !== null && stockOrigen < cantidad && cantidad > 0" x-cloak
                             class="mt-1.5 text-xs text-red-500">
                            <i class="fas fa-exclamation-triangle mr-1"></i>La cantidad supera el stock disponible en origen
                        </div>
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
                        <span>El stock se descuenta del almacén origen al registrar. El destino recibirá el stock cuando <strong>confirme la recepción</strong>.</span>
                    </div>

                    {{-- Acciones --}}
                    <div class="flex justify-end gap-3 pt-2 border-t border-gray-100">
                        <a href="{{ route('traslados.index') }}"
                           class="px-5 py-2.5 border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition-colors">
                            Cancelar
                        </a>
                        <button type="submit"
                                :disabled="almacenId && almacenDestinoId && almacenId === almacenDestinoId"
                                class="px-6 py-2.5 bg-blue-700 hover:bg-blue-800 text-white text-sm font-semibold rounded-lg flex items-center gap-2 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                            <i class="fas fa-paper-plane"></i>Enviar Traslado
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

<script>
function trasladoForm() {
    const stocks    = @json($stocksData->toArray());
    const imeis     = @json($imeisData->toArray());
    const tipos     = @json($tiposInventario->toArray());

    return {
        productoId:       '{{ old('producto_id', $selectedProductoId ?? '') }}',
        almacenId:        '{{ old('almacen_id', '') }}',
        almacenDestinoId: '{{ old('almacen_destino_id', '') }}',
        cantidad:          {{ old('cantidad', 1) }},
        esSerie:          false,
        stockOrigen:      null,

        init() {
            this.onProductoChange();
        },

        onProductoChange() {
            const pid = String(this.productoId);
            this.esSerie = pid && tipos[pid] === 'serie';
            this.calcularStock();
        },

        onAlmacenChange() {
            this.calcularStock();
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
        }
    };
}
</script>
</body>
</html>
