<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuevo Pedido - CORPORACIÓN ADIVON SAC</title>
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50">
    <x-sidebar :role="auth()->user()->role->nombre" />

    <div class="md:ml-64 p-4 md:p-8">
        <x-header 
            title="Crear Pedido" 
            subtitle="Solicitud de mercadería a proveedor" 
        />

        @if($errors->any())
            <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-lg">
                <p class="font-medium"><i class="fas fa-exclamation-circle mr-2"></i>Se encontraron errores:</p>
                <ul class="mt-2 list-disc list-inside text-sm">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="max-w-5xl mx-auto" x-data="pedidoForm()">
            <form @submit.prevent="submitForm">
                {{-- Datos del Pedido --}}
                <div class="bg-white rounded-lg shadow-md overflow-hidden mb-6">
                    <div class="bg-blue-900 px-6 py-4">
                        <h2 class="text-xl font-bold text-white">
                            <i class="fas fa-info-circle mr-2"></i>Datos del Pedido
                        </h2>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Proveedor <span class="text-red-500">*</span></label>
                                <select x-model="proveedor_id" required
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    <option value="">Seleccione proveedor</option>
                                    @foreach($proveedores as $proveedor)
                                        <option value="{{ $proveedor->id }}">{{ $proveedor->ruc }} - {{ $proveedor->razon_social }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Fecha <span class="text-red-500">*</span></label>
                                <input type="date" x-model="fecha" required
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Fecha Esperada de Entrega</label>
                                <input type="date" x-model="fecha_esperada"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Observaciones</label>
                                <textarea x-model="observaciones" rows="2" placeholder="Notas para el proveedor..."
                                          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Productos --}}
                <div class="bg-white rounded-lg shadow-md overflow-hidden mb-6">
                    <div class="bg-blue-900 px-6 py-4 flex justify-between items-center">
                        <h2 class="text-xl font-bold text-white">
                            <i class="fas fa-boxes mr-2"></i>Productos del Pedido
                        </h2>
                        <button type="button" @click="agregarDetalle()"
                                class="bg-green-600 hover:bg-green-700 text-white text-sm font-semibold py-2 px-4 rounded-lg transition-colors">
                            <i class="fas fa-plus mr-1"></i>Agregar Producto
                        </button>
                    </div>
                    <div class="p-6">
                        <template x-if="detalles.length === 0">
                            <div class="text-center py-12">
                                <i class="fas fa-box-open text-6xl text-gray-300 mb-4"></i>
                                <p class="text-lg font-medium text-gray-500">No hay productos agregados</p>
                                <p class="text-sm text-gray-400 mt-2">Haga clic en "Agregar Producto" para comenzar a crear el pedido</p>
                            </div>
                        </template>

                        <template x-for="(detalle, index) in detalles" :key="index">
                            <div class="bg-gray-50 border-2 border-gray-200 rounded-lg p-5 mb-4 hover:border-blue-400 transition-colors">
                                <div class="flex justify-between items-center mb-4">
                                    <span class="text-sm font-bold text-blue-900">
                                        <i class="fas fa-cube mr-2"></i>Producto #<span x-text="index + 1"></span>
                                    </span>
                                    <button type="button" @click="detalles.splice(index, 1)"
                                            class="text-red-600 hover:text-red-700 text-sm font-semibold transition-colors">
                                        <i class="fas fa-trash mr-1"></i>Eliminar
                                    </button>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 mb-2">Producto <span class="text-red-500">*</span></label>
                                        <select x-model="detalle.producto_id" required
                                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white">
                                            <option value="">Seleccione producto</option>
                                            @foreach($productos as $producto)
                                                <option value="{{ $producto->id }}">{{ $producto->codigo }} - {{ $producto->nombre }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 mb-2">Cantidad <span class="text-red-500">*</span></label>
                                        <input type="number" x-model.number="detalle.cantidad" min="1" required
                                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 mb-2">Precio Referencial</label>
                                        <input type="number" x-model.number="detalle.precio_referencial" min="0" step="0.01" placeholder="Opcional"
                                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white">
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                {{-- Botones --}}
                <div class="flex items-center justify-end space-x-4 pt-6">
                    <a href="{{ route('pedidos.index') }}"
                       class="px-6 py-3 border-2 border-gray-300 rounded-lg text-gray-700 font-semibold hover:bg-gray-50 transition-colors">
                        <i class="fas fa-times mr-2"></i>Cancelar
                    </a>
                    <button type="submit" :disabled="guardando || detalles.length === 0"
                            class="px-6 py-3 bg-blue-900 text-white rounded-lg font-semibold hover:bg-blue-800 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                        <span x-show="!guardando"><i class="fas fa-save mr-2"></i>Crear Pedido</span>
                        <span x-show="guardando"><i class="fas fa-spinner fa-spin mr-2"></i>Guardando...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
    function pedidoForm() {
        return {
            proveedor_id: '{{ old("proveedor_id") }}',
            fecha: '{{ old("fecha", date("Y-m-d")) }}',
            fecha_esperada: '{{ old("fecha_esperada") }}',
            observaciones: '{{ old("observaciones") }}',
            detalles: [],
            guardando: false,

            agregarDetalle() {
                this.detalles.push({ producto_id: '', cantidad: 1, precio_referencial: 0 });
            },

            submitForm() {
                if (!this.proveedor_id) { alert('Seleccione un proveedor'); return; }
                if (!this.fecha) { alert('Ingrese la fecha'); return; }
                if (this.detalles.length === 0) { alert('Agregue al menos un producto'); return; }
                for (let i = 0; i < this.detalles.length; i++) {
                    if (!this.detalles[i].producto_id) { alert(`Producto #${i+1}: Seleccione un producto`); return; }
                    if (this.detalles[i].cantidad < 1) { alert(`Producto #${i+1}: Cantidad mayor a 0`); return; }
                }
                this.guardando = true;
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '{{ route("pedidos.store") }}';
                const add = (n, v) => { const i = document.createElement('input'); i.type = 'hidden'; i.name = n; i.value = v ?? ''; form.appendChild(i); };
                add('_token', '{{ csrf_token() }}');
                add('proveedor_id', this.proveedor_id);
                add('fecha', this.fecha);
                add('fecha_esperada', this.fecha_esperada);
                add('observaciones', this.observaciones);
                this.detalles.forEach((d, i) => {
                    add(`detalles[${i}][producto_id]`, d.producto_id);
                    add(`detalles[${i}][cantidad]`, d.cantidad);
                    add(`detalles[${i}][precio_referencial]`, d.precio_referencial || 0);
                });
                document.body.appendChild(form);
                form.submit();
            }
        }
    }
    </script>
</body>
</html>