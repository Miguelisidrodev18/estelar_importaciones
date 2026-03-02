<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Precios · {{ $producto->nombre }}</title>
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>[x-cloak] { display: none !important; }</style>
</head>
<body class="bg-gray-50 font-sans">

<x-sidebar :role="auth()->user()->role->nombre" />

<div class="md:ml-64 p-4 md:p-8">

    {{-- Breadcrumb --}}
    <nav class="flex items-center gap-2 text-sm text-gray-500 mb-4">
        <a href="{{ route('precios.index') }}" class="hover:text-blue-700 transition-colors">Gestión de Precios</a>
        <i class="fas fa-chevron-right text-xs text-gray-400"></i>
        <span class="text-gray-800 font-medium truncate">{{ $producto->nombre }}</span>
    </nav>

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">{{ $producto->nombre }}</h1>
            <p class="text-sm text-gray-500 mt-0.5">Gestión de precios y márgenes</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('precios.historial', $producto) }}"
               class="inline-flex items-center gap-2 px-4 py-2 bg-purple-600 text-white text-sm font-medium rounded-lg hover:bg-purple-700 transition-colors">
                <i class="fas fa-history"></i> Historial
            </a>
            <a href="{{ route('precios.index') }}"
               class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200 transition-colors">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-5 flex items-center gap-3 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-xl">
            <i class="fas fa-check-circle text-green-500"></i>
            <span class="text-sm">{{ session('success') }}</span>
        </div>
    @endif
    @if(session('error'))
        <div class="mb-5 flex items-center gap-3 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-xl">
            <i class="fas fa-exclamation-circle text-red-500"></i>
            <span class="text-sm">{{ session('error') }}</span>
        </div>
    @endif

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

        {{-- ====== COLUMNA IZQUIERDA: info + calculadora + formulario ====== --}}
        <div class="space-y-5"
             x-data="{
                 proveedorId: '',
                 precioCompra: '',
                 margen: 30,
                 impuestos: 0,
                 resultado: null,
                 busquedaProv: '',
                 resultadosProv: [],
                 abiertoDropdown: false,
                 buscandoProv: false,
                 ultimaCompra: null,
                 cargandoCompra: false,
                 varianteId: '',
                 replicar: true,

                 async buscarProveedor() {
                     if (this.busquedaProv.length < 2) {
                         this.resultadosProv = [];
                         this.abiertoDropdown = false;
                         return;
                     }
                     this.buscandoProv = true;
                     const res = await fetch('{{ route('precios.proveedores.buscar') }}?q=' + encodeURIComponent(this.busquedaProv));
                     this.resultadosProv = await res.json();
                     this.abiertoDropdown = this.resultadosProv.length > 0;
                     this.buscandoProv = false;
                 },

                 async seleccionarProveedor(prov) {
                     this.proveedorId = prov.id;
                     this.busquedaProv = prov.razon_social;
                     this.abiertoDropdown = false;
                     this.resultadosProv = [];
                     this.resultado = null;
                     this.cargandoCompra = true;
                     const res = await fetch('{{ route('precios.ultimo-precio-compra', $producto) }}?proveedor_id=' + prov.id);
                     const data = await res.json();
                     this.ultimaCompra = data.found ? data : null;
                     if (data.found) this.precioCompra = data.precio_unitario;
                     else this.precioCompra = '';
                     this.cargandoCompra = false;
                 },

                 limpiarProveedor() {
                     this.proveedorId = '';
                     this.busquedaProv = '';
                     this.abiertoDropdown = false;
                     this.resultadosProv = [];
                     this.ultimaCompra = null;
                     this.precioCompra = '';
                     this.resultado = null;
                 },

                 async calcular() {
                     if (!this.precioCompra) return;
                     const precioBase = parseFloat(this.precioCompra) * (1 + parseFloat(this.margen) / 100);
                     const precioFinal = precioBase * (1 + parseFloat(this.impuestos || 0) / 100);
                     this.resultado = {
                         precio_base: Math.round(precioBase * 100) / 100,
                         precio_final: Math.round(precioFinal * 100) / 100,
                     };
                 },

                 get precioVentaFinal() {
                     return this.resultado ? this.resultado.precio_final : 0;
                 }
             }">

            {{-- Info del producto --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="bg-gradient-to-r from-blue-900 to-blue-700 px-5 py-4">
                    <h2 class="text-sm font-semibold text-white flex items-center gap-2">
                        <i class="fas fa-box"></i> Información del Producto
                    </h2>
                </div>
                <div class="p-5 space-y-3">
                    @foreach([
                        ['Código', $producto->codigo, 'font-mono text-xs bg-gray-100 px-2 py-0.5 rounded'],
                        ['Categoría', $producto->categoria->nombre ?? '—', ''],
                        ['Marca', $producto->marca->nombre ?? '—', ''],
                        ['Modelo', $producto->modelo->nombre ?? '—', ''],
                        ['Stock', ($producto->stock_actual ?? 0) . ' und.', 'font-semibold text-blue-700'],
                    ] as [$label, $value, $extra])
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-500">{{ $label }}</span>
                        <span class="font-medium text-gray-900 {{ $extra }}">{{ $value }}</span>
                    </div>
                    @endforeach

                    @if($producto->precio_venta > 0)
                    <div class="flex items-center justify-between text-sm border-t border-gray-100 pt-3 mt-3">
                        <span class="text-gray-500">Precio actual</span>
                        <span class="font-bold text-emerald-700 text-base">S/ {{ number_format($producto->precio_venta, 2) }}</span>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Calculadora + Formulario de Registro --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="bg-gradient-to-r from-emerald-700 to-emerald-500 px-5 py-4">
                    <h2 class="text-sm font-semibold text-white flex items-center gap-2">
                        <i class="fas fa-calculator"></i> Registrar Precio
                    </h2>
                </div>

                <form method="POST" action="{{ route('precios.store', $producto) }}" class="p-5 space-y-4">
                    @csrf

                    {{-- Variante (si el producto tiene variantes) --}}
                    @if($producto->variantesActivas->isNotEmpty())
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">
                            Variante <span class="text-gray-400 normal-case font-normal">(opcional — vacío = todas)</span>
                        </label>
                        <select name="variante_id" x-model="varianteId"
                                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500">
                            <option value="">— Precio base del producto —</option>
                            @foreach($producto->variantesActivas as $v)
                                <option value="{{ $v->id }}">
                                    {{ $v->nombre_completo }}
                                    @if($v->sobreprecio > 0) (+S/ {{ number_format($v->sobreprecio,2) }}) @endif
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @else
                        <input type="hidden" name="variante_id" value="">
                    @endif

                    {{-- Búsqueda dinámica de proveedor --}}
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">
                            Proveedor <span class="text-gray-400 normal-case font-normal">(opcional)</span>
                        </label>
                        <input type="hidden" name="proveedor_id" :value="proveedorId">
                        <div class="relative" @click.away="abiertoDropdown = false">
                            <input type="text"
                                   x-model="busquedaProv"
                                   @input.debounce.400ms="buscarProveedor()"
                                   @focus="if (resultadosProv.length) abiertoDropdown = true"
                                   placeholder="Buscar por nombre o RUC..."
                                   class="w-full border border-gray-200 rounded-lg px-3 py-2 pr-8 text-sm focus:ring-2 focus:ring-emerald-500">
                            <div class="absolute right-3 top-1/2 -translate-y-1/2">
                                <i x-show="buscandoProv || cargandoCompra" class="fas fa-spinner fa-spin text-gray-400 text-xs"></i>
                                <i x-show="!buscandoProv && !cargandoCompra && proveedorId"
                                   @click="limpiarProveedor()"
                                   class="fas fa-times text-gray-400 text-xs cursor-pointer hover:text-red-500 transition-colors"></i>
                                <i x-show="!buscandoProv && !cargandoCompra && !proveedorId"
                                   class="fas fa-search text-gray-400 text-xs"></i>
                            </div>
                            <div x-show="abiertoDropdown" x-cloak
                                 class="absolute z-20 w-full mt-1 bg-white border border-gray-200 rounded-lg shadow-lg max-h-48 overflow-y-auto">
                                <template x-for="prov in resultadosProv" :key="prov.id">
                                    <button type="button" @click="seleccionarProveedor(prov)"
                                            class="w-full text-left px-3 py-2.5 hover:bg-emerald-50 transition-colors border-b border-gray-50 last:border-0">
                                        <div class="text-sm font-medium text-gray-800" x-text="prov.razon_social"></div>
                                        <div class="text-xs text-gray-400" x-text="'RUC: ' + prov.ruc"></div>
                                    </button>
                                </template>
                            </div>
                            <div x-show="proveedorId && !abiertoDropdown" class="mt-1.5">
                                <span class="inline-flex items-center gap-1 text-xs text-emerald-700 bg-emerald-50 border border-emerald-200 px-2 py-0.5 rounded-full">
                                    <i class="fas fa-check-circle text-[9px]"></i>
                                    <span x-text="busquedaProv"></span>
                                </span>
                            </div>
                        </div>
                    </div>

                    {{-- Precio compra --}}
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">Precio Compra (S/)</label>
                        <input type="number" name="precio_compra" x-model="precioCompra"
                               step="0.01" min="0.01" placeholder="0.00" required
                               class="w-full rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 {{ $errors->has('precio_compra') ? 'border-2 border-red-400' : 'border border-gray-200' }}">
                        @error('precio_compra') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror

                        <div x-show="ultimaCompra" class="mt-1.5 flex items-start gap-1.5 text-xs text-blue-700 bg-blue-50 border border-blue-200 px-2.5 py-1.5 rounded-lg">
                            <i class="fas fa-shopping-cart mt-0.5 shrink-0"></i>
                            <span>Última compra: <strong x-text="'S/ ' + Number(ultimaCompra?.precio_unitario).toFixed(2)"></strong>
                            · <span x-text="ultimaCompra?.fecha_compra"></span></span>
                        </div>
                    </div>

                    {{-- Margen e impuestos --}}
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">Margen %</label>
                            <input type="number" name="margen" x-model="margen"
                                   step="0.1" min="0" max="1000"
                                   class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">Impuestos %</label>
                            <input type="number" x-model="impuestos"
                                   step="0.1" min="0" max="100"
                                   class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500">
                        </div>
                    </div>

                    {{-- Botón calcular --}}
                    <button type="button" @click="calcular()"
                            :disabled="!precioCompra"
                            class="w-full py-2 bg-slate-100 text-slate-700 text-sm font-semibold rounded-lg hover:bg-slate-200 transition-colors disabled:opacity-40 border border-slate-200">
                        <i class="fas fa-calculator mr-1"></i> Calcular precio de venta
                    </button>

                    {{-- Resultado calculado --}}
                    <template x-if="resultado">
                        <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-4 space-y-2">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Precio base</span>
                                <span class="font-medium" x-text="'S/ ' + resultado.precio_base.toFixed(2)"></span>
                            </div>
                            <div class="flex justify-between text-sm border-t border-emerald-200 pt-2">
                                <span class="font-semibold text-gray-700">Precio de venta final</span>
                                <span class="font-bold text-emerald-700 text-lg" x-text="'S/ ' + resultado.precio_final.toFixed(2)"></span>
                            </div>
                        </div>
                    </template>

                    {{-- Campo oculto precio_venta (del resultado) --}}
                    <input type="hidden" name="precio_venta" :value="resultado ? resultado.precio_final : ''">

                    {{-- Precio mayorista --}}
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">
                            Precio Mayorista (S/) <span class="text-gray-400 normal-case font-normal">(opcional)</span>
                        </label>
                        <input type="number" name="precio_mayorista" step="0.01" min="0.01" placeholder="0.00"
                               class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500">
                    </div>

                    {{-- Observaciones --}}
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">Observaciones</label>
                        <textarea name="observaciones" rows="2" placeholder="Motivo del precio, notas..."
                                  class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 resize-none"></textarea>
                    </div>

                    {{-- Replicar a tiendas --}}
                    <label class="flex items-start gap-3 cursor-pointer p-3 rounded-xl border border-emerald-200 bg-emerald-50 hover:bg-emerald-100 transition-colors">
                        <input type="checkbox" name="replicar_tiendas" value="1" x-model="replicar"
                               class="mt-0.5 h-4 w-4 text-emerald-600 border-gray-300 rounded focus:ring-emerald-500">
                        <div>
                            <p class="text-sm font-semibold text-emerald-800">Replicar a todas las tiendas</p>
                            <p class="text-xs text-emerald-600 mt-0.5">
                                Se creará el mismo precio en cada sucursal activa.
                                Cada tienda podrá modificarlo después.
                            </p>
                        </div>
                    </label>

                    {{-- Botón guardar --}}
                    <button type="submit"
                            :disabled="!resultado"
                            class="w-full py-3 bg-emerald-600 text-white font-bold text-sm rounded-xl hover:bg-emerald-700 transition-colors disabled:opacity-40 disabled:cursor-not-allowed shadow-sm">
                        <i class="fas fa-save mr-2"></i>
                        Guardar Precio
                    </button>

                    <p x-show="!resultado" x-cloak class="text-xs text-center text-gray-400">
                        Ingresa el precio de compra y haz clic en Calcular para habilitar el guardado.
                    </p>
                </form>
            </div>
        </div>

        {{-- ====== COLUMNA DERECHA: tabla de precios ====== --}}
        <div class="xl:col-span-2 space-y-5">

            {{-- KPI cards --}}
            @php
                $precioGlobal = $preciosGlobales->where('activo', true)->first();
                $totalRegistros = $producto->precios->count();
            @endphp
            <div class="grid grid-cols-3 gap-4">
                <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 text-center">
                    <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Precio Venta Global</p>
                    <p class="text-xl font-bold text-blue-700">
                        {{ $precioGlobal ? 'S/ ' . number_format($precioGlobal->precio, 2) : '—' }}
                    </p>
                </div>
                <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 text-center">
                    <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Precio Compra</p>
                    <p class="text-xl font-bold text-gray-700">
                        {{ $precioGlobal ? 'S/ ' . number_format($precioGlobal->precio_compra, 2) : '—' }}
                    </p>
                </div>
                <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 text-center">
                    <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Margen</p>
                    <p class="text-xl font-bold {{ ($precioGlobal?->margen ?? 0) >= 20 ? 'text-green-700' : 'text-yellow-600' }}">
                        {{ $precioGlobal ? $precioGlobal->margen . '%' : '—' }}
                    </p>
                </div>
            </div>

            {{-- PRECIOS GLOBALES (sin tienda) --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="bg-gradient-to-r from-blue-900 to-blue-700 px-5 py-4 flex items-center justify-between">
                    <h2 class="text-sm font-semibold text-white flex items-center gap-2">
                        <i class="fas fa-globe"></i> Precio Global (todas las tiendas)
                    </h2>
                    <span class="text-xs text-blue-200">{{ $preciosGlobales->count() }} registro(s)</span>
                </div>

                @if($preciosGlobales->count())
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Variante</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Proveedor</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">P. Compra</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">P. Venta</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">P. Mayor.</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Margen</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Estado</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Acción</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($preciosGlobales as $precio)
                            <tr class="hover:bg-blue-50/30 transition-colors {{ $precio->activo ? '' : 'opacity-50' }}">
                                <td class="px-4 py-3 text-sm text-gray-700">
                                    @if($precio->variante)
                                        <div class="flex items-center gap-1.5">
                                            @if($precio->variante->color)
                                                <div class="w-3 h-3 rounded-full border border-gray-300"
                                                     style="background:{{ $precio->variante->color->codigo_hex ?? '#999' }}"></div>
                                            @endif
                                            <span class="font-medium">{{ $precio->variante->nombre_completo }}</span>
                                        </div>
                                    @else
                                        <span class="text-gray-400 italic text-xs">Base</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-600">{{ $precio->proveedor?->razon_social ?? '—' }}</td>
                                <td class="px-4 py-3 text-sm text-right text-gray-700">
                                    {{ $precio->precio_compra ? 'S/ ' . number_format($precio->precio_compra, 2) : '—' }}
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <span class="text-sm font-bold text-blue-700">S/ {{ number_format($precio->precio, 2) }}</span>
                                </td>
                                <td class="px-4 py-3 text-sm text-right text-gray-600">
                                    {{ $precio->precio_mayorista ? 'S/ ' . number_format($precio->precio_mayorista, 2) : '—' }}
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <span class="text-sm font-semibold {{ ($precio->margen ?? 0) >= 20 ? 'text-green-700' : 'text-yellow-700' }}">
                                        {{ $precio->margen ? $precio->margen . '%' : '—' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    @if($precio->activo)
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-green-50 text-green-700 border border-green-200">
                                            <i class="fas fa-circle text-[6px]"></i> Activo
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-500 border border-gray-200">
                                            <i class="fas fa-circle text-[6px]"></i> Inactivo
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <a href="{{ route('precios.edit', ['producto' => $producto->id, 'precio' => $precio->id]) }}"
                                       class="inline-flex items-center gap-1 px-3 py-1 bg-yellow-50 text-yellow-700 border border-yellow-200 rounded-lg text-xs font-medium hover:bg-yellow-100 transition-colors">
                                        <i class="fas fa-edit"></i> Editar
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="py-12 text-center">
                    <div class="w-14 h-14 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-3">
                        <i class="fas fa-globe text-2xl text-gray-400"></i>
                    </div>
                    <p class="text-gray-500 font-medium text-sm">Sin precio global registrado</p>
                    <p class="text-gray-400 text-xs mt-1">Usa el formulario de la izquierda para registrar el primer precio.</p>
                </div>
                @endif
            </div>

            {{-- PRECIOS POR TIENDA --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="bg-gradient-to-r from-indigo-700 to-indigo-500 px-5 py-4 flex items-center justify-between">
                    <h2 class="text-sm font-semibold text-white flex items-center gap-2">
                        <i class="fas fa-store"></i> Precio por Tienda / Sucursal
                    </h2>
                    <span class="text-xs text-indigo-200">{{ $preciosPorTienda->count() }} registro(s)</span>
                </div>

                @if($preciosPorTienda->count())
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Tienda</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Variante</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">P. Venta</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Margen</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Estado</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Acción</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($preciosPorTienda->sortBy(fn($p) => $p->almacen?->nombre) as $precio)
                            <tr class="hover:bg-indigo-50/20 transition-colors {{ $precio->activo ? '' : 'opacity-50' }}">
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        <div class="w-7 h-7 rounded-lg bg-indigo-100 flex items-center justify-center">
                                            <i class="fas fa-store text-indigo-600 text-xs"></i>
                                        </div>
                                        <div>
                                            <p class="text-sm font-semibold text-gray-800">{{ $precio->almacen?->nombre ?? '—' }}</p>
                                            <p class="text-xs text-gray-400">{{ $precio->almacen?->tipo ?? '' }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-700">
                                    @if($precio->variante)
                                        <span class="font-medium">{{ $precio->variante->nombre_completo }}</span>
                                    @else
                                        <span class="text-gray-400 italic text-xs">Base</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <span class="text-sm font-bold text-indigo-700">S/ {{ number_format($precio->precio, 2) }}</span>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <span class="text-sm font-semibold {{ ($precio->margen ?? 0) >= 20 ? 'text-green-700' : 'text-yellow-700' }}">
                                        {{ $precio->margen ? $precio->margen . '%' : '—' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    @if($precio->activo)
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-green-50 text-green-700 border border-green-200">
                                            <i class="fas fa-circle text-[6px]"></i> Activo
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-500 border border-gray-200">
                                            <i class="fas fa-circle text-[6px]"></i> Inactivo
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <a href="{{ route('precios.edit', ['producto' => $producto->id, 'precio' => $precio->id]) }}"
                                       class="inline-flex items-center gap-1 px-3 py-1 bg-indigo-50 text-indigo-700 border border-indigo-200 rounded-lg text-xs font-medium hover:bg-indigo-100 transition-colors">
                                        <i class="fas fa-edit"></i> Editar
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="py-10 text-center">
                    <div class="w-14 h-14 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-3">
                        <i class="fas fa-store text-2xl text-gray-400"></i>
                    </div>
                    <p class="text-gray-500 font-medium text-sm">Sin precios por tienda</p>
                    <p class="text-gray-400 text-xs mt-1">
                        Activa "Replicar a todas las tiendas" al guardar un precio global para crear precios individuales por sucursal.
                    </p>
                </div>
                @endif
            </div>

        </div>
    </div>
</div>
</body>
</html>
