<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Nueva Venta · POS</title>
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        [x-cloak] { display: none !important; }
        .line-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        ::-webkit-scrollbar { width: 4px; height: 4px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #334155; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #475569; }
    </style>
</head>
<body class="bg-slate-900 font-sans antialiased h-screen overflow-hidden"
      x-data="posApp()"
      x-init="init()">

{{-- ========== TOP BAR ========== --}}
<header class="h-14 bg-slate-950 border-b border-slate-800 flex items-center justify-between px-4 flex-shrink-0 z-20 shadow-lg">
    <div class="flex items-center gap-3">
        <a href="{{ route('ventas.index') }}"
           class="w-8 h-8 flex items-center justify-center rounded-lg text-slate-400 hover:text-white hover:bg-slate-800 transition-colors">
            <i class="fas fa-arrow-left text-sm"></i>
        </a>
        <button @click="procesarPago()"
                :disabled="carrito.length === 0 || !almacenId || guardando"
                class="bg-blue-600 hover:bg-blue-500 disabled:opacity-40 disabled:cursor-not-allowed text-white px-5 py-1.5 rounded-lg text-sm font-bold transition-colors">
            Registrar
        </button>
        <button class="text-slate-400 hover:text-white px-3 py-1.5 rounded-lg text-sm border border-slate-700 hover:border-slate-600 transition-colors">
            Órdenes
        </button>
        <span class="bg-slate-800 text-slate-300 px-3 py-1 rounded-lg text-sm font-mono border border-slate-700">
            #<span x-text="ordenNumero"></span>
        </span>
    </div>

    <div class="flex items-center gap-4">
        {{-- Search --}}
        <div class="relative">
            <input type="text"
                   x-model="busqueda"
                   x-ref="searchInput"
                   @keydown.enter.prevent="buscarProductoDirecto()"
                   placeholder="Buscar por nombre o código... (F2)"
                   class="w-80 bg-slate-800 text-white placeholder-slate-500 border border-slate-700 rounded-xl pl-9 pr-8 py-2 text-sm focus:outline-none focus:border-blue-500 focus:bg-slate-750 transition-colors">
            <i class="fas fa-search absolute left-3 top-2.5 text-slate-500 text-sm pointer-events-none"></i>
            <button x-show="busqueda" @click="busqueda=''" x-cloak
                    class="absolute right-3 top-2.5 text-slate-500 hover:text-white transition-colors">
                <i class="fas fa-times text-xs"></i>
            </button>
        </div>
        {{-- User --}}
        <div class="flex items-center gap-2">
            <div class="w-8 h-8 rounded-full bg-purple-700 flex items-center justify-center text-white text-sm font-bold shadow">
                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
            </div>
            <span class="text-slate-300 text-sm hidden md:block">{{ auth()->user()->name }}</span>
        </div>
    </div>
</header>

{{-- ========== MAIN LAYOUT ========== --}}
<div class="flex" style="height: calc(100vh - 3.5rem)">

    {{-- ====== LEFT: CART ====== --}}
    <aside class="w-72 xl:w-80 flex-shrink-0 bg-slate-900 border-r border-slate-800 flex flex-col shadow-xl z-10">

        {{-- Almacén --}}
        <div class="px-4 py-3 border-b border-slate-800">
            <select x-model="almacenId"
                    class="w-full bg-slate-800 text-white border border-slate-700 rounded-xl px-3 py-2.5 text-sm font-semibold focus:outline-none focus:border-blue-500 cursor-pointer uppercase tracking-wide">
                <option value="">— SELECCIONAR ALMACÉN —</option>
                @foreach($almacenes as $alm)
                    <option value="{{ $alm->id }}">{{ strtoupper($alm->nombre) }}</option>
                @endforeach
            </select>
        </div>

        {{-- Cart items --}}
        <div class="flex-1 overflow-y-auto">
            <template x-if="carrito.length === 0">
                <div class="flex flex-col items-center justify-center h-full py-12 text-slate-600 select-none">
                    <div class="w-16 h-16 bg-slate-800 rounded-2xl flex items-center justify-center mb-3">
                        <i class="fas fa-shopping-cart text-2xl text-slate-600"></i>
                    </div>
                    <p class="text-sm font-medium">Carrito vacío</p>
                    <p class="text-xs mt-1 text-slate-700">Selecciona productos del catálogo</p>
                </div>
            </template>

            <div class="p-3 space-y-2">
                <template x-for="(item, index) in carrito" :key="index">
                    <div class="bg-slate-800 rounded-xl p-3 border border-slate-700 hover:border-slate-600 transition-colors">
                        <div class="flex justify-between items-start mb-2">
                            <p class="text-sm font-semibold text-white leading-tight pr-2 line-clamp-2" x-text="item.nombre"></p>
                            <button @click="eliminarDelCarrito(index)"
                                    class="text-slate-600 hover:text-red-400 flex-shrink-0 transition-colors mt-0.5">
                                <i class="fas fa-times text-xs"></i>
                            </button>
                        </div>
                        <template x-if="item.imeis && item.imeis.length">
                            <div class="mb-2 flex flex-wrap gap-1">
                                <template x-for="imei in item.imeis">
                                    <span class="bg-purple-900/60 text-purple-300 text-[10px] px-1.5 py-0.5 rounded font-mono" x-text="imei"></span>
                                </template>
                            </div>
                        </template>
                        <div class="flex items-center justify-between mt-1">
                            <div class="flex items-center bg-slate-700 rounded-lg overflow-hidden">
                                <button @click="decrementarCantidad(index)"
                                        class="w-8 h-8 flex items-center justify-center text-white hover:bg-slate-600 transition-colors">
                                    <i class="fas fa-minus text-xs"></i>
                                </button>
                                <span class="w-8 text-center text-sm font-bold text-white" x-text="item.cantidad"></span>
                                <button @click="incrementarCantidad(index)"
                                        class="w-8 h-8 flex items-center justify-center text-white hover:bg-slate-600 transition-colors">
                                    <i class="fas fa-plus text-xs"></i>
                                </button>
                            </div>
                            <div class="text-right">
                                <p class="text-[11px] text-slate-400" x-text="'S/ ' + item.precio_unitario.toFixed(2) + ' c/u'"></p>
                                <p class="text-sm font-bold text-white" x-text="'S/ ' + (item.cantidad * item.precio_unitario).toFixed(2)"></p>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        {{-- Totals + Actions --}}
        <div class="border-t border-slate-800 p-4 space-y-3 bg-slate-900/80">
            <div class="space-y-1.5">
                <div class="flex justify-between text-sm">
                    <span class="text-slate-400">Subtotal</span>
                    <span class="text-slate-200 font-medium" x-text="'S/ ' + subtotal.toFixed(2)"></span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-slate-400">Impuestos (IGV 18%)</span>
                    <span class="text-slate-200 font-medium" x-text="'S/ ' + igv.toFixed(2)"></span>
                </div>
                <div class="flex justify-between items-center pt-2 border-t border-slate-700">
                    <span class="text-base font-bold text-white">Total</span>
                    <span class="text-xl font-bold text-white" x-text="'S/ ' + total.toFixed(2)"></span>
                </div>
            </div>

            {{-- Client + Note --}}
            <div class="flex gap-2">
                <div class="flex-1 relative" x-data="{ open: false }">
                    <button @click="open = !open"
                            class="w-full border border-slate-700 hover:border-slate-600 text-slate-300 hover:text-white bg-slate-800 hover:bg-slate-750 rounded-xl py-2 px-3 text-sm flex items-center gap-2 transition-colors">
                        <i class="fas fa-user text-xs text-slate-400"></i>
                        <span class="truncate text-left flex-1" x-text="clienteNombre || 'Cliente'"></span>
                        <i class="fas fa-chevron-down text-xs text-slate-500"></i>
                    </button>
                    <div x-show="open" @click.outside="open=false" x-cloak
                         class="absolute bottom-full left-0 w-64 bg-slate-800 border border-slate-600 rounded-xl shadow-2xl mb-2 z-30 p-3">
                        <p class="text-xs text-slate-400 mb-2 font-semibold uppercase tracking-wider">Seleccionar cliente</p>
                        <select x-model="clienteId"
                                @change="clienteNombre = $event.target.selectedIndex > 0 ? $event.target.options[$event.target.selectedIndex].text : ''; open = false"
                                class="w-full bg-slate-700 text-white border border-slate-600 rounded-lg px-2 py-2 text-sm focus:outline-none focus:border-blue-500">
                            <option value="">Cliente general</option>
                            @foreach($clientes as $cli)
                                <option value="{{ $cli->id }}">{{ $cli->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <button @click="showNota = !showNota"
                        :class="observaciones ? 'border-blue-500 text-blue-400 bg-blue-900/20' : 'border-slate-700 text-slate-400 bg-slate-800'"
                        class="border hover:border-slate-500 hover:text-white rounded-xl px-3 py-2 text-sm flex items-center gap-1.5 transition-colors">
                    <i class="fas fa-sticky-note text-xs"></i>
                    Nota
                </button>
            </div>

            {{-- Nota textarea --}}
            <div x-show="showNota" x-cloak>
                <textarea x-model="observaciones"
                          rows="2"
                          placeholder="Observaciones de la venta..."
                          class="w-full bg-slate-800 text-white border border-slate-700 rounded-xl px-3 py-2 text-sm focus:outline-none focus:border-blue-500 resize-none placeholder-slate-600"></textarea>
            </div>

            {{-- Pay button --}}
            <button @click="procesarPago()"
                    :disabled="carrito.length === 0 || !almacenId || guardando"
                    class="w-full bg-purple-600 hover:bg-purple-500 disabled:opacity-40 disabled:cursor-not-allowed text-white py-3.5 rounded-xl font-bold text-base flex items-center justify-center gap-2 transition-colors shadow-lg shadow-purple-900/40">
                <i class="fas fa-cash-register"></i>
                <span x-show="!guardando">Pago  <kbd class="text-xs opacity-60 font-normal ml-1">F8</kbd></span>
                <span x-show="guardando" x-cloak>
                    <i class="fas fa-spinner fa-spin mr-1"></i> Procesando...
                </span>
            </button>
        </div>
    </aside>

    {{-- ====== RIGHT: PRODUCTS ====== --}}
    <main class="flex-1 flex flex-col overflow-hidden" style="background-color: #0f172a;">

        {{-- Categories scroll --}}
        <div class="flex-shrink-0 px-4 py-3 border-b border-slate-800 overflow-x-auto">
            <div class="flex items-center gap-2 min-w-max">
                <button @click="categoriaActiva = null"
                        :class="categoriaActiva === null
                            ? 'bg-purple-600 text-white border-purple-600 shadow-lg shadow-purple-900/30'
                            : 'text-slate-400 border-slate-700 hover:text-white hover:border-slate-600'"
                        class="px-5 py-1.5 rounded-full text-sm font-semibold transition-all whitespace-nowrap border">
                    Todos
                </button>
                @foreach($categorias as $cat)
                    <button @click="categoriaActiva = {{ $cat->id }}"
                            :class="categoriaActiva === {{ $cat->id }}
                                ? 'bg-purple-600 text-white border-purple-600 shadow-lg shadow-purple-900/30'
                                : 'text-slate-400 border-slate-700 hover:text-white hover:border-slate-600'"
                            class="px-5 py-1.5 rounded-full text-sm font-semibold transition-all whitespace-nowrap border">
                        {{ strtoupper($cat->nombre) }}
                    </button>
                @endforeach
            </div>
        </div>

        {{-- Products grid --}}
        <div class="flex-1 overflow-y-auto p-4">
            <div class="grid grid-cols-3 sm:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-3">
                <template x-for="producto in productosFiltrados" :key="producto.id">
                    <div @click="agregarAlCarrito(producto)"
                         :class="producto.stock_actual === 0 && producto.tipo_inventario !== 'serie'
                             ? 'opacity-40 cursor-not-allowed'
                             : 'cursor-pointer hover:border-purple-500 hover:shadow-lg hover:shadow-purple-900/20 hover:-translate-y-0.5'"
                         class="bg-slate-800 border border-slate-700 rounded-xl overflow-hidden transition-all duration-150 group select-none">
                        {{-- Image/icon --}}
                        <div class="aspect-square flex items-center justify-center relative overflow-hidden"
                             style="background-color: #1e293b;">
                            <template x-if="producto.imagen">
                                <img :src="producto.imagen" :alt="producto.nombre" class="w-full h-full object-cover">
                            </template>
                            <template x-if="!producto.imagen">
                                <i class="fas fa-box text-slate-600 text-3xl group-hover:text-slate-500 transition-colors"></i>
                            </template>
                            <span x-show="producto.tipo_inventario === 'serie'" x-cloak
                                  class="absolute top-1.5 right-1.5 bg-purple-600 text-white text-[10px] px-1.5 py-0.5 rounded-md font-bold tracking-wide">
                                IMEI
                            </span>
                            <span x-show="producto.stock_actual === 0 && producto.tipo_inventario !== 'serie'" x-cloak
                                  class="absolute bottom-1.5 left-1.5 bg-red-600/80 text-white text-[10px] px-1.5 py-0.5 rounded-md">
                                Sin stock
                            </span>
                        </div>
                        {{-- Info --}}
                        <div class="p-2.5">
                            <p class="text-[11px] text-slate-300 font-medium line-clamp-2 leading-tight mb-1.5" x-text="producto.nombre"></p>
                            <p class="text-sm font-bold text-white" x-text="'S/ ' + producto.precio_venta.toFixed(2)"></p>
                        </div>
                    </div>
                </template>
            </div>

            {{-- Empty state --}}
            <div x-show="productosFiltrados.length === 0" x-cloak
                 class="flex flex-col items-center justify-center py-20 text-slate-700 select-none">
                <i class="fas fa-box-open text-5xl mb-3"></i>
                <p class="text-base font-medium">No hay productos</p>
                <p class="text-sm mt-1 text-slate-800">Prueba con otra categoría o búsqueda</p>
            </div>
        </div>
    </main>
</div>

{{-- ========== MODAL: PAGO ========== --}}
<div x-show="showPago" x-cloak
     class="fixed inset-0 z-50 flex items-center justify-center">
    <div class="absolute inset-0 bg-black/70 backdrop-blur-sm" @click="showPago = false"></div>
    <div class="relative bg-slate-800 border border-slate-700 rounded-2xl w-full max-w-sm shadow-2xl mx-4">
        <div class="flex items-start justify-between p-5 border-b border-slate-700">
            <div>
                <h3 class="text-lg font-bold text-white">Procesar Pago</h3>
                <p class="text-sm text-slate-400 mt-0.5">Selecciona el método de pago</p>
            </div>
            <div class="text-right">
                <p class="text-2xl font-bold text-white" x-text="'S/ ' + total.toFixed(2)"></p>
                <button @click="showPago = false" class="text-slate-500 hover:text-slate-300 text-xs mt-1 transition-colors">
                    ✕ cerrar
                </button>
            </div>
        </div>
        <div class="p-5 space-y-4">
            <div class="grid grid-cols-2 gap-2">
                <button @click="metodoPago = 'efectivo'"
                        :class="metodoPago === 'efectivo' ? 'bg-purple-600 border-purple-500 text-white shadow-lg shadow-purple-900/30' : 'bg-slate-700 border-slate-600 text-slate-300 hover:bg-slate-650 hover:border-slate-500'"
                        class="border rounded-xl py-3 font-semibold text-sm transition-all flex items-center justify-center gap-2">
                    <i class="fas fa-money-bill-wave"></i> Efectivo
                </button>
                <button @click="metodoPago = 'transferencia'"
                        :class="metodoPago === 'transferencia' ? 'bg-purple-600 border-purple-500 text-white shadow-lg shadow-purple-900/30' : 'bg-slate-700 border-slate-600 text-slate-300 hover:bg-slate-650'"
                        class="border rounded-xl py-3 font-semibold text-sm transition-all flex items-center justify-center gap-2">
                    <i class="fas fa-university"></i> Transferencia
                </button>
                <button @click="metodoPago = 'yape'"
                        :class="metodoPago === 'yape' ? 'bg-purple-600 border-purple-500 text-white shadow-lg shadow-purple-900/30' : 'bg-slate-700 border-slate-600 text-slate-300 hover:bg-slate-650'"
                        class="border rounded-xl py-3 font-semibold text-sm transition-all flex items-center justify-center gap-2">
                    <i class="fas fa-mobile-alt"></i> Yape
                </button>
                <button @click="metodoPago = 'plin'"
                        :class="metodoPago === 'plin' ? 'bg-purple-600 border-purple-500 text-white shadow-lg shadow-purple-900/30' : 'bg-slate-700 border-slate-600 text-slate-300 hover:bg-slate-650'"
                        class="border rounded-xl py-3 font-semibold text-sm transition-all flex items-center justify-center gap-2">
                    <i class="fas fa-mobile-alt"></i> Plin
                </button>
            </div>

            <div x-show="metodoPago === 'efectivo'" x-cloak class="space-y-2">
                <label class="text-sm font-medium text-slate-300">Monto recibido</label>
                <input type="number" x-model.number="montoRecibido" step="0.50"
                       class="w-full bg-slate-700 border border-slate-600 text-white text-2xl font-bold rounded-xl px-4 py-3 focus:outline-none focus:border-purple-500 text-center">
                <div class="flex justify-between bg-slate-700/60 rounded-xl px-4 py-2.5 text-sm">
                    <span class="text-slate-400">Vuelto</span>
                    <span class="font-bold text-lg"
                          :class="montoRecibido >= total ? 'text-green-400' : 'text-red-400'"
                          x-text="'S/ ' + Math.max(0, montoRecibido - total).toFixed(2)"></span>
                </div>
            </div>

            <div class="flex gap-3 pt-1">
                <button @click="showPago = false"
                        class="flex-1 border border-slate-600 text-slate-300 hover:bg-slate-700 rounded-xl py-3 font-semibold text-sm transition-colors">
                    Cancelar
                </button>
                <button @click="confirmarPago()" :disabled="!metodoPago || guardando"
                        class="flex-1 bg-purple-600 hover:bg-purple-500 disabled:opacity-50 text-white rounded-xl py-3 font-bold text-sm transition-colors">
                    <i class="fas fa-check mr-2"></i>Confirmar Venta
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ========== MODAL: VARIANTES ========== --}}
<div x-show="mostrarModalVariante" x-cloak
     class="fixed inset-0 z-50 flex items-center justify-center">
    <div class="absolute inset-0 bg-black/70 backdrop-blur-sm" @click="mostrarModalVariante = false"></div>
    <div class="relative bg-slate-800 border border-slate-700 rounded-2xl w-full max-w-md shadow-2xl mx-4">
        <div class="p-5 border-b border-slate-700">
            <h3 class="text-base font-bold text-white flex items-center gap-2">
                <i class="fas fa-layer-group text-indigo-400"></i>
                Seleccionar Variante
            </h3>
            <p class="text-sm text-slate-400 mt-0.5 truncate" x-text="productoActual ? productoActual.nombre : ''"></p>
        </div>
        <div class="p-5 space-y-3">
            <template x-for="v in (productoActual ? productoActual.variantes : [])" :key="v.id">
                <button @click="seleccionarVariante(v)"
                        :disabled="!v.tiene_stock && productoActual.tipo_inventario !== 'serie'"
                        :class="!v.tiene_stock && productoActual.tipo_inventario !== 'serie'
                            ? 'opacity-40 cursor-not-allowed border-slate-600'
                            : 'hover:border-indigo-400 hover:bg-indigo-900/20 cursor-pointer'"
                        class="w-full text-left border border-slate-600 rounded-xl px-4 py-3 transition-all">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-7 h-7 rounded-full border-2 border-slate-500 flex-shrink-0"
                                 :style="v.color_hex ? `background-color:${v.color_hex}` : 'background:#475569'"></div>
                            <div>
                                <p class="text-sm font-semibold text-white" x-text="v.nombre_completo"></p>
                                <p class="text-xs text-slate-400 font-mono" x-text="v.sku"></p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-xs font-semibold"
                               :class="v.stock_actual > 0 ? 'text-green-400' : 'text-red-400'"
                               x-text="v.stock_actual + ' en stock'"></p>
                            <template x-if="v.sobreprecio > 0">
                                <p class="text-xs text-indigo-300">+S/ <span x-text="v.sobreprecio.toFixed(2)"></span></p>
                            </template>
                        </div>
                    </div>
                </button>
            </template>
        </div>
        <div class="px-5 pb-5">
            <button @click="mostrarModalVariante = false"
                    class="w-full border border-slate-600 text-slate-300 hover:bg-slate-700 rounded-xl py-2.5 text-sm font-semibold transition-colors">
                Cancelar
            </button>
        </div>
    </div>
</div>

{{-- ========== MODAL: IMEI ========== --}}
<div x-show="mostrarModalIMEI" x-cloak
     class="fixed inset-0 z-50 flex items-center justify-center">
    <div class="absolute inset-0 bg-black/70 backdrop-blur-sm" @click="mostrarModalIMEI = false; imeisTemp = []"></div>
    <div class="relative bg-slate-800 border border-slate-700 rounded-2xl w-full max-w-md shadow-2xl mx-4">
        <div class="p-5 border-b border-slate-700">
            <h3 class="text-lg font-bold text-white">Ingresar IMEIs</h3>
            <p class="text-sm text-slate-400 mt-0.5 truncate"
               x-text="productoActual
                   ? (productoActual.nombre + (varianteActual && varianteActual.nombre_completo ? ' — ' + varianteActual.nombre_completo : ''))
                   : ''"></p>
        </div>
        <div class="p-5 space-y-4">
            <div class="flex gap-2">
                <input type="text" x-model="imeiActual"
                       @keydown.enter.prevent="agregarIMEI()"
                       maxlength="15"
                       placeholder="15 dígitos numéricos"
                       class="flex-1 bg-slate-700 border border-slate-600 text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-purple-500 font-mono placeholder-slate-500 tracking-widest">
                <button @click="agregarIMEI()"
                        class="bg-purple-600 hover:bg-purple-500 text-white px-4 rounded-xl font-semibold text-sm transition-colors">
                    <i class="fas fa-plus"></i>
                </button>
            </div>

            <div class="space-y-1.5 max-h-48 overflow-y-auto">
                <template x-for="(imei, i) in imeisTemp" :key="i">
                    <div class="flex items-center justify-between bg-slate-700 rounded-lg px-3 py-2.5">
                        <div class="flex items-center gap-2">
                            <span class="text-purple-400 text-xs font-bold" x-text="(i+1) + '.'"></span>
                            <span class="text-sm font-mono text-white tracking-widest" x-text="imei"></span>
                        </div>
                        <button @click="eliminarIMEI(i)" class="text-slate-500 hover:text-red-400 transition-colors">
                            <i class="fas fa-trash-alt text-xs"></i>
                        </button>
                    </div>
                </template>
                <div x-show="imeisTemp.length === 0" x-cloak
                     class="text-center text-slate-600 text-sm py-6">
                    <i class="fas fa-microchip text-2xl mb-2 block"></i>
                    Sin IMEIs ingresados
                </div>
            </div>

            <div class="flex gap-3">
                <button @click="mostrarModalIMEI = false; imeisTemp = []"
                        class="flex-1 border border-slate-600 text-slate-300 hover:bg-slate-700 rounded-xl py-2.5 font-semibold text-sm transition-colors">
                    Cancelar
                </button>
                <button @click="confirmarIMEIs()"
                        :disabled="imeisTemp.length === 0"
                        class="flex-1 bg-purple-600 hover:bg-purple-500 disabled:opacity-40 text-white rounded-xl py-2.5 font-bold text-sm transition-colors">
                    <i class="fas fa-check mr-1"></i>
                    Agregar <span x-text="imeisTemp.length ? '(' + imeisTemp.length + ')' : ''"></span>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function posApp() {
    return {
        busqueda:         '',
        categoriaActiva:  null,
        almacenId:        '{{ auth()->user()->almacen_id ?? "" }}',
        clienteId:        '',
        clienteNombre:    '',
        observaciones:    '',
        showNota:         false,
        carrito:          [],
        productos:        @json($productos),
        guardando:        false,
        showPago:         false,
        metodoPago:       'efectivo',
        montoRecibido:    0,
        ordenNumero:      Math.floor(1000 + Math.random() * 9000),
        mostrarModalIMEI:      false,
        mostrarModalVariante:  false,
        productoActual:        null,
        varianteActual:        null,
        imeiActual:            '',
        imeisTemp:             [],

        get productosFiltrados() {
            return this.productos.filter(p => {
                if (this.categoriaActiva !== null && p.categoria_id !== this.categoriaActiva) return false;
                if (this.busqueda.trim()) {
                    const s = this.busqueda.toLowerCase();
                    return p.nombre.toLowerCase().includes(s) ||
                           (p.codigo && p.codigo.toLowerCase().includes(s)) ||
                           (p.codigo_barras && String(p.codigo_barras).includes(s));
                }
                return true;
            });
        },

        get subtotal() { return this.carrito.reduce((s, i) => s + i.cantidad * i.precio_unitario, 0); },
        get igv()      { return this.subtotal * 0.18; },
        get total()    { return this.subtotal + this.igv; },

        init() {
            document.addEventListener('keydown', e => {
                if (e.key === 'F2') { e.preventDefault(); this.$refs.searchInput?.focus(); }
                if (e.key === 'F8') { e.preventDefault(); if (this.carrito.length > 0) this.procesarPago(); }
            });
        },

        buscarProductoDirecto() {
            if (!this.busqueda.trim()) return;
            const found = this.productosFiltrados[0];
            if (found) { this.agregarAlCarrito(found); this.busqueda = ''; }
        },

        agregarAlCarrito(producto) {
            if (!this.almacenId) { alert('Selecciona un almacén primero'); return; }

            // Si el producto tiene variantes, mostrar el selector de variante primero
            if (producto.tiene_variantes && producto.variantes && producto.variantes.length > 0) {
                this.productoActual      = producto;
                this.varianteActual      = null;
                this.mostrarModalVariante = true;
                return;
            }

            if (producto.stock_actual === 0 && producto.tipo_inventario !== 'serie') return;

            if (producto.tipo_inventario === 'serie') {
                this.productoActual = producto;
                this.mostrarModalIMEI = true;
                return;
            }

            const existente = this.carrito.find(i => i.producto_id === producto.id && !i.variante_id);
            if (existente) {
                if (existente.cantidad < producto.stock_actual) existente.cantidad++;
                else alert('Stock máximo alcanzado');
            } else {
                this.carrito.push({
                    producto_id:      producto.id,
                    variante_id:      null,
                    nombre:           producto.nombre,
                    precio_unitario:  producto.precio_venta,
                    cantidad:         1,
                    stock_disponible: producto.stock_actual,
                    tipo_inventario:  producto.tipo_inventario,
                    imeis: []
                });
            }
        },

        seleccionarVariante(v) {
            this.varianteActual       = v;
            this.mostrarModalVariante = false;

            const precioFinal = parseFloat(this.productoActual.precio_venta) + parseFloat(v.sobreprecio || 0);
            const nombreCompleto = this.productoActual.nombre +
                (v.nombre_completo ? ' — ' + v.nombre_completo : '');

            if (this.productoActual.tipo_inventario === 'serie') {
                // Para productos con IMEI, abrir modal de IMEI tras seleccionar variante
                this.mostrarModalIMEI = true;
                return;
            }

            // Producto de tipo cantidad
            if (!v.tiene_stock) { alert('Esta variante no tiene stock disponible'); return; }

            const existente = this.carrito.find(i =>
                i.producto_id === this.productoActual.id && i.variante_id === v.id
            );
            if (existente) {
                if (existente.cantidad < v.stock_actual) existente.cantidad++;
                else alert('Stock máximo alcanzado');
            } else {
                this.carrito.push({
                    producto_id:      this.productoActual.id,
                    variante_id:      v.id,
                    nombre:           nombreCompleto,
                    precio_unitario:  precioFinal,
                    cantidad:         1,
                    stock_disponible: v.stock_actual,
                    tipo_inventario:  this.productoActual.tipo_inventario,
                    imeis: []
                });
            }
            this.productoActual = null;
            this.varianteActual = null;
        },

        incrementarCantidad(index) {
            const item = this.carrito[index];
            if (item.tipo_inventario !== 'serie' && item.cantidad >= item.stock_disponible) {
                alert('Stock máximo alcanzado'); return;
            }
            item.cantidad++;
        },

        decrementarCantidad(index) {
            if (this.carrito[index].cantidad > 1) this.carrito[index].cantidad--;
            else this.eliminarDelCarrito(index);
        },

        eliminarDelCarrito(index) { this.carrito.splice(index, 1); },

        procesarPago() {
            if (this.carrito.length === 0) { alert('Agrega productos al carrito'); return; }
            if (!this.almacenId) { alert('Selecciona un almacén'); return; }
            this.showPago   = true;
            this.metodoPago = 'efectivo';
            this.montoRecibido = parseFloat(this.total.toFixed(2));
        },

        async confirmarPago() {
            if (!this.metodoPago) return;
            this.guardando = true;
            this.showPago  = false;
            try {
                const res = await fetch('{{ route("ventas.store") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        almacen_id:    this.almacenId,
                        cliente_id:    this.clienteId || null,
                        observaciones: this.observaciones,
                        metodo_pago:   this.metodoPago,
                        detalles:      this.carrito.map(i => ({
                            producto_id:     i.producto_id,
                            variante_id:     i.variante_id || null,
                            cantidad:        i.cantidad,
                            precio_unitario: i.precio_unitario,
                            imeis:           i.imeis || []
                        }))
                    })
                });
                const data = await res.json();
                if (res.ok) {
                    window.location.href = '/ventas/' + data.venta_id;
                } else {
                    alert(data.error || data.message || 'Error al procesar la venta');
                    this.guardando = false;
                }
            } catch(e) {
                console.error(e);
                alert('Error de conexión');
                this.guardando = false;
            }
        },

        async agregarIMEI() {
            if (!this.imeiActual) return;
            if (!/^\d{15}$/.test(this.imeiActual)) { alert('El IMEI debe tener exactamente 15 dígitos numéricos'); return; }
            if (this.imeisTemp.includes(this.imeiActual)) { alert('Este IMEI ya fue ingresado'); return; }
            this.imeisTemp.push(this.imeiActual);
            this.imeiActual = '';
        },

        eliminarIMEI(i) { this.imeisTemp.splice(i, 1); },

        confirmarIMEIs() {
            if (!this.imeisTemp.length) return;

            const v = this.varianteActual;
            const precioFinal = parseFloat(this.productoActual.precio_venta) +
                (v ? parseFloat(v.sobreprecio || 0) : 0);
            const nombreCompleto = this.productoActual.nombre +
                (v && v.nombre_completo ? ' — ' + v.nombre_completo : '');

            this.carrito.push({
                producto_id:      this.productoActual.id,
                variante_id:      v ? v.id : null,
                nombre:           nombreCompleto,
                precio_unitario:  precioFinal,
                cantidad:         this.imeisTemp.length,
                stock_disponible: this.imeisTemp.length,
                tipo_inventario:  'serie',
                imeis:            [...this.imeisTemp]
            });
            this.mostrarModalIMEI = false;
            this.productoActual   = null;
            this.varianteActual   = null;
            this.imeiActual       = '';
            this.imeisTemp        = [];
        }
    }
}
</script>
</body>
</html>
