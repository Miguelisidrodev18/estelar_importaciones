<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Movimiento - CORPORACIÓN ADIVON SAC</title>
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50">
    <x-sidebar :role="auth()->user()->role->nombre" />

    <div class="ml-64 p-8">
        <x-header 
            title="Registrar Movimiento de Inventario" 
            subtitle="Ingreso, salida, ajuste o transferencia de productos" 
        />

        <div class="max-w-4xl mx-auto">
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="bg-blue-900 px-6 py-4">
                    <h2 class="text-xl font-bold text-white">
                        <i class="fas fa-exchange-alt mr-2"></i>
                        Nuevo Movimiento
                    </h2>
                </div>

                <form action="{{ route('inventario.movimientos.store') }}" method="POST" class="p-6" id="movimientoForm">
                    @csrf

                    <!-- Tipo de Movimiento -->
                    <div class="mb-8">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4 pb-2 border-b border-gray-200">
                            <i class="fas fa-list-ul mr-2 text-blue-900"></i>
                            Tipo de Movimiento
                        </h3>

                        <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                            <label class="cursor-pointer">
                                <input type="radio" name="tipo_movimiento" value="ingreso" class="peer hidden" required onchange="handleTipoChange()">
                                <div class="border-2 border-gray-300 rounded-lg p-4 text-center hover:border-green-500 peer-checked:border-green-500 peer-checked:bg-green-50 transition-all">
                                    <i class="fas fa-arrow-down text-3xl text-green-600 mb-2"></i>
                                    <p class="font-semibold text-gray-900">Ingreso</p>
                                    <p class="text-xs text-gray-500">Entrada de stock</p>
                                </div>
                            </label>

                            <label class="cursor-pointer">
                                <input type="radio" name="tipo_movimiento" value="salida" class="peer hidden" onchange="handleTipoChange()">
                                <div class="border-2 border-gray-300 rounded-lg p-4 text-center hover:border-red-500 peer-checked:border-red-500 peer-checked:bg-red-50 transition-all">
                                    <i class="fas fa-arrow-up text-3xl text-red-600 mb-2"></i>
                                    <p class="font-semibold text-gray-900">Salida</p>
                                    <p class="text-xs text-gray-500">Salida de stock</p>
                                </div>
                            </label>

                            <label class="cursor-pointer">
                                <input type="radio" name="tipo_movimiento" value="ajuste" class="peer hidden" onchange="handleTipoChange()">
                                <div class="border-2 border-gray-300 rounded-lg p-4 text-center hover:border-blue-500 peer-checked:border-blue-500 peer-checked:bg-blue-50 transition-all">
                                    <i class="fas fa-sliders-h text-3xl text-blue-600 mb-2"></i>
                                    <p class="font-semibold text-gray-900">Ajuste</p>
                                    <p class="text-xs text-gray-500">Corrección manual</p>
                                </div>
                            </label>

                            <label class="cursor-pointer">
                                <input type="radio" name="tipo_movimiento" value="transferencia" class="peer hidden" onchange="handleTipoChange()">
                                <div class="border-2 border-gray-300 rounded-lg p-4 text-center hover:border-purple-500 peer-checked:border-purple-500 peer-checked:bg-purple-50 transition-all">
                                    <i class="fas fa-exchange-alt text-3xl text-purple-600 mb-2"></i>
                                    <p class="font-semibold text-gray-900">Transferencia</p>
                                    <p class="text-xs text-gray-500">Entre almacenes</p>
                                </div>
                            </label>

                            <label class="cursor-pointer">
                                <input type="radio" name="tipo_movimiento" value="devolucion" class="peer hidden" onchange="handleTipoChange()">
                                <div class="border-2 border-gray-300 rounded-lg p-4 text-center hover:border-orange-500 peer-checked:border-orange-500 peer-checked:bg-orange-50 transition-all">
                                    <i class="fas fa-undo text-3xl text-orange-600 mb-2"></i>
                                    <p class="font-semibold text-gray-900">Devolución</p>
                                    <p class="text-xs text-gray-500">Retorno de producto</p>
                                </div>
                            </label>

                            <label class="cursor-pointer">
                                <input type="radio" name="tipo_movimiento" value="merma" class="peer hidden" onchange="handleTipoChange()">
                                <div class="border-2 border-gray-300 rounded-lg p-4 text-center hover:border-gray-500 peer-checked:border-gray-500 peer-checked:bg-gray-50 transition-all">
                                    <i class="fas fa-exclamation-triangle text-3xl text-gray-600 mb-2"></i>
                                    <p class="font-semibold text-gray-900">Merma</p>
                                    <p class="text-xs text-gray-500">Pérdida/deterioro</p>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Datos del Movimiento -->
                    <div class="mb-8">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4 pb-2 border-b border-gray-200">
                            <i class="fas fa-info-circle mr-2 text-blue-900"></i>
                            Datos del Movimiento
                        </h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Producto -->
                            <div>
                                <label for="producto_id" class="block text-sm font-medium text-gray-700 mb-2">
                                    Producto <span class="text-red-500">*</span>
                                </label>
                                <select name="producto_id" id="producto_id" 
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                        required onchange="loadProductInfo()">
                                    <option value="">Seleccione un producto</option>
                                    @foreach($productos as $producto)
                                        <option value="{{ $producto->id }}" 
                                                data-stock="{{ $producto->stock_actual }}" 
                                                data-unidad="{{ $producto->unidad_medida }}"
                                                data-tipo="{{ $producto->tipo_producto }}">
                                            {{ $producto->codigo }} - {{ $producto->nombre }} (Stock: {{ $producto->stock_actual }})
                                        </option>
                                    @endforeach
                                </select>
                                
                                <div id="productoInfo" class="mt-2 hidden">
                                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-3">
                                        <p class="text-sm text-blue-900">
                                            <i class="fas fa-info-circle mr-2"></i>
                                            <span id="tipoProducto"></span> - Stock: <span id="stockActual" class="font-bold">0</span> <span id="unidadMedida"></span>
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Almacén -->
                            <div>
                                <label for="almacen_id" class="block text-sm font-medium text-gray-700 mb-2">
                                    Almacén <span class="text-red-500">*</span>
                                </label>
                                <select name="almacen_id" id="almacen_id" 
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                        required>
                                    <option value="">Seleccione un almacén</option>
                                    @foreach($almacenes as $almacen)
                                        <option value="{{ $almacen->id }}">{{ $almacen->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- IMEI (solo para celulares) -->
                            <div id="imeiDiv" class="hidden">
                                <label for="imei_id" class="block text-sm font-medium text-gray-700 mb-2">
                                    IMEI <span class="text-red-500">*</span>
                                </label>
                                <select name="imei_id" id="imei_id" 
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                    <option value="">Primero seleccione producto y almacén</option>
                                </select>
                                <p class="mt-1 text-xs text-gray-500">Solo para productos tipo celular</p>
                            </div>

                            <!-- Cantidad (solo para accesorios) -->
                            <div id="cantidadDiv">
                                <label for="cantidad" class="block text-sm font-medium text-gray-700 mb-2">
                                    Cantidad <span class="text-red-500">*</span>
                                </label>
                                <input type="number" name="cantidad" id="cantidad" min="1" value="1"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            </div>

                            <!-- Almacén Destino (solo transferencias) -->
                            <div id="almacenDestinoDiv" class="hidden">
                                <label for="almacen_destino_id" class="block text-sm font-medium text-gray-700 mb-2">
                                    Almacén Destino <span class="text-red-500">*</span>
                                </label>
                                <select name="almacen_destino_id" id="almacen_destino_id" 
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                    <option value="">Seleccione almacén destino</option>
                                    @foreach($almacenes as $almacen)
                                        <option value="{{ $almacen->id }}">{{ $almacen->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Motivo -->
                            <div class="md:col-span-2">
                                <label for="motivo" class="block text-sm font-medium text-gray-700 mb-2">
                                    Motivo <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="motivo" id="motivo" 
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                       placeholder="Ej: Compra a proveedor, Venta a cliente..." required>
                            </div>

                            <!-- Observaciones -->
                            <div class="md:col-span-2">
                                <label for="observaciones" class="block text-sm font-medium text-gray-700 mb-2">Observaciones</label>
                                <textarea name="observaciones" id="observaciones" rows="2"
                                          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                          placeholder="Información adicional..."></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Advertencia -->
                    <div class="mb-6 bg-yellow-50 border-l-4 border-yellow-500 p-4 rounded-lg">
                        <div class="flex">
                            <i class="fas fa-exclamation-triangle text-yellow-500 mt-0.5 mr-3"></i>
                            <div class="text-sm text-yellow-700">
                                <p class="font-medium">Importante:</p>
                                <p class="mt-1">Los movimientos NO se pueden eliminar. Para celulares, se registrará el IMEI específico.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Botones -->
                    <div class="flex items-center justify-end space-x-3 pt-6 border-t border-gray-200">
                        <a href="{{ route('inventario.movimientos.index') }}" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                            <i class="fas fa-times mr-2"></i>Cancelar
                        </a>
                        <button type="submit" class="px-6 py-2 bg-blue-900 text-white rounded-lg hover:bg-blue-800">
                            <i class="fas fa-save mr-2"></i>Registrar Movimiento
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        let tipoProductoActual = '';

        function handleTipoChange() {
            const tipo = document.querySelector('input[name="tipo_movimiento"]:checked');
            const almacenDestinoDiv = document.getElementById('almacenDestinoDiv');
            const almacenDestinoSelect = document.getElementById('almacen_destino_id');
            
            if (tipo && tipo.value === 'transferencia') {
                almacenDestinoDiv.classList.remove('hidden');
                almacenDestinoSelect.required = true;
            } else {
                almacenDestinoDiv.classList.add('hidden');
                almacenDestinoSelect.required = false;
            }
        }

        function loadProductInfo() {
            const select = document.getElementById('producto_id');
            const option = select.options[select.selectedIndex];
            const productoInfo = document.getElementById('productoInfo');
            const stockActual = document.getElementById('stockActual');
            const unidadMedida = document.getElementById('unidadMedida');
            const tipoProducto = document.getElementById('tipoProducto');
            const imeiDiv = document.getElementById('imeiDiv');
            const cantidadDiv = document.getElementById('cantidadDiv');
            const imeiSelect = document.getElementById('imei_id');
            
            if (option.value) {
                const stock = option.dataset.stock;
                const unidad = option.dataset.unidad;
                const tipo = option.dataset.tipo;
                
                tipoProductoActual = tipo;
                
                stockActual.textContent = stock;
                unidadMedida.textContent = unidad;
                tipoProducto.textContent = tipo === 'celular' ? 'Celular' : 'Accesorio';
                productoInfo.classList.remove('hidden');
                
                // Mostrar/ocultar campos según tipo
                if (tipo === 'celular') {
                    imeiDiv.classList.remove('hidden');
                    cantidadDiv.classList.add('hidden');
                    imeiSelect.required = true;
                    document.getElementById('cantidad').required = false;
                    document.getElementById('cantidad').value = 1;
                } else {
                    imeiDiv.classList.add('hidden');
                    cantidadDiv.classList.remove('hidden');
                    imeiSelect.required = false;
                    document.getElementById('cantidad').required = true;
                }
            } else {
                productoInfo.classList.add('hidden');
                imeiDiv.classList.add('hidden');
                cantidadDiv.classList.remove('hidden');
            }
        }
    </script>
</body>
</html>