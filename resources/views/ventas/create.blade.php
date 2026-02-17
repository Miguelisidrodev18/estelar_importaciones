<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Punto de Venta - CORPORACIÓN ADIVON SAC</title>
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        .pos-scrollbar::-webkit-scrollbar { width: 6px; }
        .pos-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .pos-scrollbar::-webkit-scrollbar-thumb { background: #4b5563; border-radius: 3px; }
    </style>
</head>
<body class="bg-gray-900 overflow-hidden" x-data="posApp()">

    @if(session('error'))
        <div class="fixed top-4 right-4 z-50 bg-red-600 text-white px-6 py-3 rounded-lg shadow-lg" x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)">
            <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
        </div>
    @endif

    <div class="h-screen flex flex-col">
        {{-- Top Bar --}}
        <div class="bg-gray-800 border-b border-gray-700 px-4 py-2 flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <a href="{{ route('ventas.index') }}" class="text-gray-400 hover:text-white transition-colors">
                    <i class="fas fa-arrow-left text-lg"></i>
                </a>
                <div class="flex items-center space-x-1">
                    <button @click="vista = 'pos'" :class="vista === 'pos' ? 'bg-blue-600 text-white' : 'text-gray-400 hover:text-white'"
                            class="px-4 py-1.5 rounded-lg text-sm font-medium transition-colors">
                        Registrar
                    </button>
                    <a href="{{ route('ventas.index') }}" class="px-4 py-1.5 rounded-lg text-sm font-medium text-gray-400 hover:text-white transition-colors">
                        Órdenes
                    </a>
                </div>
                <span class="bg-gray-700 text-gray-300 px-3 py-1 rounded-lg text-sm font-mono">
                    #<span x-text="ordenNumero"></span>
                </span>
            </div>
            <div class="flex items-center space-x-3">
                <div class="relative">
                    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-500"></i>
                    <input type="text" x-model="busqueda" placeholder="Buscar productos..."
                           class="bg-gray-700 text-white pl-10 pr-4 py-2 rounded-lg text-sm border-0 focus:ring-2 focus:ring-blue-500 w-48 md:w-64">
                </div>
                <div class="flex items-center space-x-2 text-gray-300">
                    <div class="bg-gray-700 rounded-full p-2">
                        <i class="fas fa-user text-sm"></i>
                    </div>
                    <span class="text-sm hidden md:inline">{{ auth()->user()->name }}</span>
                </div>
                <button @click="menuOpen = !menuOpen" class="text-gray-400 hover:text-white">
                    <i class="fas fa-bars text-lg"></i>
                </button>
            </div>
        </div>

        {{-- Main Content --}}
        <div class="flex-1 flex overflow-hidden">
            {{-- Left Panel - Cart/Ticket --}}
            <div class="w-full md:w-[380px] lg:w-[420px] bg-gray-900 flex flex-col border-r border-gray-700" :class="showCart ? '' : 'hidden md:flex'">

                {{-- Almacén selector --}}
                <div class="px-4 py-2 border-b border-gray-700">
                    <select x-model="almacenId" class="w-full bg-gray-800 text-white text-sm rounded-lg border-gray-600 focus:ring-blue-500 focus:border-blue-500 py-2">
                        <option value="">Seleccione almacén...</option>
                        @foreach($almacenes as $alm)
                            <option value="{{ $alm->id }}">{{ $alm->nombre }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Cart Items --}}
                <div class="flex-1 overflow-y-auto pos-scrollbar">
                    <template x-if="carrito.length === 0">
                        <div class="flex flex-col items-center justify-center h-full text-gray-600">
                            <i class="fas fa-shopping-cart text-5xl mb-3"></i>
                            <p class="text-sm">Carrito vacío</p>
                            <p class="text-xs mt-1">Seleccione productos para agregar</p>
                        </div>
                    </template>

                    <template x-for="(item, index) in carrito" :key="item.producto_id">
                        <div class="px-4 py-3 border-b border-gray-800 hover:bg-gray-800/50 transition-colors">
                            <div class="flex items-center justify-between">
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between">
                                        <p class="text-white text-sm font-medium truncate" x-text="item.nombre"></p>
                                        <p class="text-white text-sm font-bold ml-2 whitespace-nowrap" x-text="'S/ ' + (item.cantidad * item.precio_unitario).toFixed(2)"></p>
                                    </div>
                                    <div class="flex items-center justify-between mt-2">
                                        <div class="flex items-center space-x-2">
                                            <button @click="decrementarCantidad(index)" class="w-7 h-7 rounded bg-gray-700 text-gray-300 hover:bg-gray-600 flex items-center justify-center text-xs">
                                                <i class="fas fa-minus"></i>
                                            </button>
                                            <input type="number" x-model.number="item.cantidad" min="1"
                                                   class="w-12 text-center bg-gray-800 text-white rounded border-gray-600 text-sm py-1">
                                            <button @click="item.cantidad++" class="w-7 h-7 rounded bg-gray-700 text-gray-300 hover:bg-gray-600 flex items-center justify-center text-xs">
                                                <i class="fas fa-plus"></i>
                                            </button>
                                        </div>
                                        <div class="flex items-center space-x-2">
                                            <span class="text-gray-500 text-xs" x-text="'S/ ' + item.precio_unitario.toFixed(2) + ' c/u'"></span>
                                            <button @click="eliminarDelCarrito(index)" class="text-red-500 hover:text-red-400 text-sm">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                {{-- Totals & Actions --}}
                <div class="border-t border-gray-700">
                    <div class="px-4 py-2 space-y-1">
                        <div class="flex justify-between text-gray-400 text-sm">
                            <span>Impuestos</span>
                            <span x-text="'S/ ' + igv.toFixed(2)"></span>
                        </div>
                        <div class="flex justify-between text-white text-lg font-bold">
                            <span>Total</span>
                            <span x-text="'S/ ' + total.toFixed(2)"></span>
                        </div>
                    </div>

                    <div class="px-4 py-2 flex items-center space-x-2 border-t border-gray-700">
                        <button @click="mostrarClientes = !mostrarClientes" class="bg-gray-800 text-gray-300 hover:bg-gray-700 px-3 py-2 rounded-lg text-sm transition-colors">
                            <i class="fas fa-user mr-1"></i>Cliente
                        </button>
                        <button @click="mostrarNota = !mostrarNota" class="bg-gray-800 text-gray-300 hover:bg-gray-700 px-3 py-2 rounded-lg text-sm transition-colors">
                            <i class="fas fa-sticky-note mr-1"></i>Nota
                        </button>
                        <div class="flex-1"></div>
                    </div>

                    {{-- Cliente selector (toggle) --}}
                    <div x-show="mostrarClientes" x-transition class="px-4 py-2 border-t border-gray-700">
                        <select x-model="clienteId" class="w-full bg-gray-800 text-white text-sm rounded-lg border-gray-600 focus:ring-blue-500 py-2">
                            <option value="">Sin cliente</option>
                            @foreach($clientes as $cli)
                                <option value="{{ $cli->id }}">{{ $cli->nombre }} ({{ $cli->numero_documento }})</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Nota (toggle) --}}
                    <div x-show="mostrarNota" x-transition class="px-4 py-2 border-t border-gray-700">
                        <textarea x-model="observaciones" rows="2" placeholder="Nota para la venta..."
                                  class="w-full bg-gray-800 text-white text-sm rounded-lg border-gray-600 focus:ring-blue-500 py-2"></textarea>
                    </div>

                    <div class="p-4">
                        <button @click="procesarPago()" :disabled="carrito.length === 0 || !almacenId || guardando"
                                class="w-full py-4 rounded-lg text-lg font-bold transition-colors disabled:opacity-40 disabled:cursor-not-allowed"
                                :class="carrito.length > 0 && almacenId ? 'bg-purple-700 hover:bg-purple-600 text-white' : 'bg-gray-700 text-gray-500'">
                            <span x-show="!guardando">
                                <i class="fas fa-credit-card mr-2"></i>Pago
                            </span>
                            <span x-show="guardando">
                                <i class="fas fa-spinner fa-spin mr-2"></i>Procesando...
                            </span>
                        </button>
                    </div>
                </div>
            </div>

            {{-- Right Panel - Products --}}
            <div class="flex-1 bg-gray-800 flex flex-col overflow-hidden" :class="showCart ? 'hidden md:flex' : ''">

                {{-- Category filters --}}
                <div class="px-4 py-3 border-b border-gray-700 overflow-x-auto flex items-center space-x-2 flex-shrink-0">
                    <button @click="categoriaActiva = null"
                            :class="categoriaActiva === null ? 'bg-blue-600 text-white' : 'bg-gray-700 text-gray-300 hover:bg-gray-600'"
                            class="px-4 py-2 rounded-lg text-sm font-medium whitespace-nowrap transition-colors">
                        Todos
                    </button>
                    @foreach($categorias as $cat)
                        <button @click="categoriaActiva = {{ $cat->id }}"
                                :class="categoriaActiva === {{ $cat->id }} ? 'bg-blue-600 text-white' : 'bg-gray-700 text-gray-300 hover:bg-gray-600'"
                                class="px-4 py-2 rounded-lg text-sm font-medium whitespace-nowrap transition-colors">
                            {{ $cat->nombre }}
                        </button>
                    @endforeach
                </div>

                {{-- Product Grid --}}
                <div class="flex-1 overflow-y-auto p-4 pos-scrollbar">
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-3">
                        @foreach($productos as $prod)
                            <div x-show="filtrarProducto({{ $prod->id }}, {{ $prod->categoria_id ?? 'null' }}, '{{ addslashes($prod->nombre) }}')"
                                 @click="agregarAlCarrito({{ $prod->id }}, '{{ addslashes($prod->nombre) }}', {{ $prod->precio_venta ?? 0 }}, '{{ $prod->tipo_producto }}')"
                                 class="bg-gray-700 rounded-lg overflow-hidden cursor-pointer hover:bg-gray-600 hover:ring-2 hover:ring-blue-500 transition-all group">
                                <div class="aspect-square bg-gray-600 flex items-center justify-center relative">
                                    @if($prod->imagen)
                                        <img src="{{ asset('storage/' . $prod->imagen) }}" alt="{{ $prod->nombre }}" class="w-full h-full object-cover">
                                    @else
                                        <i class="fas fa-box text-4xl text-gray-500 group-hover:text-gray-400"></i>
                                    @endif
                                    @if($prod->tipo_producto === 'celular')
                                        <span class="absolute top-1 right-1 bg-purple-600 text-white text-xs px-1.5 py-0.5 rounded">
                                            <i class="fas fa-mobile-alt"></i>
                                        </span>
                                    @endif
                                </div>
                                <div class="p-2">
                                    <p class="text-white text-xs font-medium truncate">{{ $prod->nombre }}</p>
                                    <p class="text-gray-400 text-xs mt-0.5">S/ {{ number_format($prod->precio_venta ?? 0, 2) }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- Empty state --}}
                    <div x-show="productosVisibles === 0" class="flex flex-col items-center justify-center py-16 text-gray-500">
                        <i class="fas fa-search text-5xl mb-3"></i>
                        <p class="text-lg">No se encontraron productos</p>
                        <p class="text-sm mt-1">Intente con otro término de búsqueda</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Mobile toggle button --}}
        <button @click="showCart = !showCart"
                class="md:hidden fixed bottom-4 right-4 z-40 bg-blue-600 text-white w-14 h-14 rounded-full shadow-lg flex items-center justify-center">
            <span x-show="!showCart" class="relative">
                <i class="fas fa-shopping-cart text-xl"></i>
                <span x-show="carrito.length > 0" class="absolute -top-2 -right-2 bg-red-500 text-white text-xs w-5 h-5 rounded-full flex items-center justify-center"
                      x-text="carrito.length"></span>
            </span>
            <span x-show="showCart"><i class="fas fa-th text-xl"></i></span>
        </button>
    </div>

    {{-- Hidden form for submission --}}
    <form id="ventaForm" action="{{ route('ventas.store') }}" method="POST" class="hidden">
        @csrf
        <input type="hidden" name="cliente_id" :value="clienteId">
        <input type="hidden" name="almacen_id" :value="almacenId">
        <input type="hidden" name="observaciones" :value="observaciones">
    </form>

    <script>
    function posApp() {
        return {
            vista: 'pos',
            busqueda: '',
            categoriaActiva: null,
            almacenId: '{{ $almacenes->first()->id ?? '' }}',
            clienteId: '',
            observaciones: '',
            carrito: [],
            guardando: false,
            showCart: false,
            mostrarClientes: false,
            mostrarNota: false,
            menuOpen: false,
            ordenNumero: Math.floor(1000 + Math.random() * 9000),
            productosVisibles: {{ $productos->count() }},

            get subtotal() {
                return this.carrito.reduce((sum, item) => sum + (item.cantidad * item.precio_unitario), 0);
            },
            get igv() {
                return 0;
            },
            get total() {
                return this.subtotal + this.igv;
            },

            filtrarProducto(id, catId, nombre) {
                let visible = true;
                if (this.categoriaActiva !== null && catId !== this.categoriaActiva) {
                    visible = false;
                }
                if (this.busqueda.trim() !== '' && !nombre.toLowerCase().includes(this.busqueda.toLowerCase())) {
                    visible = false;
                }
                return visible;
            },

            agregarAlCarrito(productoId, nombre, precio, tipo) {
                const existente = this.carrito.find(item => item.producto_id === productoId);
                if (existente && tipo !== 'celular') {
                    existente.cantidad++;
                } else {
                    this.carrito.push({
                        producto_id: productoId,
                        nombre: nombre,
                        precio_unitario: precio,
                        cantidad: 1,
                        es_celular: tipo === 'celular'
                    });
                }
                this.showCart = true;
            },

            decrementarCantidad(index) {
                if (this.carrito[index].cantidad > 1) {
                    this.carrito[index].cantidad--;
                } else {
                    this.eliminarDelCarrito(index);
                }
            },

            eliminarDelCarrito(index) {
                this.carrito.splice(index, 1);
            },

            procesarPago() {
                if (this.carrito.length === 0) {
                    alert('Agregue al menos un producto');
                    return;
                }
                if (!this.almacenId) {
                    alert('Seleccione un almacén');
                    return;
                }

                this.guardando = true;

                const form = document.getElementById('ventaForm');

                // Remove old detail inputs
                form.querySelectorAll('.detalle-input').forEach(el => el.remove());

                // Add cart items as hidden inputs
                this.carrito.forEach((item, i) => {
                    const addInput = (name, value) => {
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = name;
                        input.value = value ?? '';
                        input.className = 'detalle-input';
                        form.appendChild(input);
                    };
                    addInput(`detalles[${i}][producto_id]`, item.producto_id);
                    addInput(`detalles[${i}][cantidad]`, item.cantidad);
                    addInput(`detalles[${i}][precio_unitario]`, item.precio_unitario);
                });

                // Update hidden fields
                form.querySelector('[name="cliente_id"]').value = this.clienteId;
                form.querySelector('[name="almacen_id"]').value = this.almacenId;
                form.querySelector('[name="observaciones"]').value = this.observaciones;

                form.submit();
            }
        }
    }
    </script>
</body>
</html>
