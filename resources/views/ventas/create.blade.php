<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva Venta - Sistema de Importaciones</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body class="bg-gray-100">
    <x-sidebar :role="auth()->user()->role->nombre" />
    <x-header title="Nueva Venta" />

    <div class="ml-64 p-8 pt-24">
        @if(session('error'))
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded"><i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}</div>
        @endif

        <div class="flex items-center mb-6">
            <a href="{{ route('ventas.index') }}" class="text-blue-600 hover:text-blue-800 mr-4"><i class="fas fa-arrow-left"></i></a>
            <h2 class="text-2xl font-bold text-gray-800">Registrar Venta</h2>
        </div>

        <form action="{{ route('ventas.store') }}" method="POST" x-data="ventaForm()">
            @csrf
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="bg-white rounded-xl shadow-md p-6">
                    <h3 class="text-lg font-semibold text-gray-700 mb-4">Datos de la Venta</h3>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Cliente (opcional)</label>
                            <select name="cliente_id" class="w-full rounded-lg border-gray-300 shadow-sm">
                                <option value="">Sin cliente</option>
                                @foreach($clientes as $cli)
                                    <option value="{{ $cli->id }}">{{ $cli->nombre }} ({{ $cli->numero_documento }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Almacén *</label>
                            <select name="almacen_id" x-model="almacenId" required class="w-full rounded-lg border-gray-300 shadow-sm">
                                <option value="">Seleccione...</option>
                                @foreach($almacenes as $alm)
                                    <option value="{{ $alm->id }}">{{ $alm->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Observaciones</label>
                            <textarea name="observaciones" rows="3" class="w-full rounded-lg border-gray-300 shadow-sm"></textarea>
                        </div>

                        <div class="bg-blue-50 rounded-lg p-4 mt-4">
                            <p class="text-sm text-gray-500">Total</p>
                            <p class="text-2xl font-bold text-blue-600" x-text="'S/ ' + total.toFixed(2)"></p>
                            <p class="text-xs text-gray-400 mt-1">El pago se confirma en sucursal</p>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-2 bg-white rounded-xl shadow-md p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-gray-700">Productos</h3>
                        <button type="button" @click="agregarDetalle()" class="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded-lg text-sm">
                            <i class="fas fa-plus mr-1"></i>Agregar
                        </button>
                    </div>

                    <template x-for="(detalle, index) in detalles" :key="index">
                        <div class="border rounded-lg p-4 mb-3 bg-gray-50">
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                                <div>
                                    <label class="block text-xs text-gray-500 mb-1">Producto *</label>
                                    <select :name="'detalles['+index+'][producto_id]'" x-model="detalle.producto_id" required @change="onProductoChange(index)"
                                            class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                                        <option value="">Seleccione...</option>
                                        @foreach($productos as $prod)
                                            <option value="{{ $prod->id }}" data-tipo="{{ $prod->tipo_producto }}" data-precio="{{ $prod->precio_venta }}">{{ $prod->nombre }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-500 mb-1">Cantidad *</label>
                                    <input type="number" :name="'detalles['+index+'][cantidad]'" x-model.number="detalle.cantidad" min="1" required
                                           class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-500 mb-1">Precio *</label>
                                    <input type="number" :name="'detalles['+index+'][precio_unitario]'" x-model.number="detalle.precio_unitario" min="0.01" step="0.01" required
                                           class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                                </div>
                                <div class="flex items-end gap-2">
                                    <div class="flex-1">
                                        <label class="block text-xs text-gray-500 mb-1">Subtotal</label>
                                        <p class="py-2 text-sm font-semibold" x-text="'S/ ' + (detalle.cantidad * detalle.precio_unitario).toFixed(2)"></p>
                                    </div>
                                    <button type="button" @click="eliminarDetalle(index)" class="text-red-500 hover:text-red-700 pb-2" x-show="detalles.length > 1">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>

                            <template x-if="detalle.es_celular">
                                <div class="mt-3 border-t pt-3">
                                    <label class="block text-xs font-semibold text-purple-600 mb-1"><i class="fas fa-mobile-alt mr-1"></i>Seleccionar IMEI</label>
                                    <select :name="'detalles['+index+'][imei_id]'" class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                                        <option value="">Cargando IMEIs...</option>
                                    </select>
                                    <p class="text-xs text-gray-400 mt-1">Los IMEIs disponibles se cargan según almacén y producto</p>
                                </div>
                            </template>
                        </div>
                    </template>

                    <div class="flex justify-end gap-3 mt-6">
                        <a href="{{ route('ventas.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-semibold py-2 px-6 rounded-lg">Cancelar</a>
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-6 rounded-lg"><i class="fas fa-save mr-2"></i>Registrar Venta</button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <script>
    function ventaForm() {
        return {
            almacenId: '',
            detalles: [{ producto_id: '', cantidad: 1, precio_unitario: 0, es_celular: false }],
            get total() { return this.detalles.reduce((sum, d) => sum + (d.cantidad * d.precio_unitario), 0); },
            agregarDetalle() { this.detalles.push({ producto_id: '', cantidad: 1, precio_unitario: 0, es_celular: false }); },
            eliminarDetalle(i) { this.detalles.splice(i, 1); },
            onProductoChange(i) {
                const sel = event.target; const opt = sel.options[sel.selectedIndex];
                this.detalles[i].es_celular = opt?.dataset?.tipo === 'celular';
                this.detalles[i].precio_unitario = parseFloat(opt?.dataset?.precio || 0);
            }
        }
    }
    </script>
</body>
</html>
