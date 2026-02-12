<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Producto - CORPORACIÓN ADIVON SAC</title>
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50">
    <x-sidebar :role="auth()->user()->role->nombre" />

    <div class="ml-64 p-8">
        <x-header title="Editar Producto" subtitle="Actualiza la información de {{ $producto->nombre }}" />

        <div class="max-w-5xl mx-auto">
            <!-- Info del Producto -->
            <div class="mb-6 bg-gray-50 p-4 rounded-lg border border-gray-200">
                <div class="grid grid-cols-4 gap-4 text-sm">
                    <div>
                        <span class="font-medium text-gray-700">Código:</span>
                        <span class="text-gray-900 ml-2">{{ $producto->codigo }}</span>
                    </div>
                    <div>
                        <span class="font-medium text-gray-700">Tipo:</span>
                        @if($producto->tipo_producto == 'celular')
                            <span class="ml-2 px-2 py-1 text-xs bg-blue-100 text-blue-800 rounded-full">
                                <i class="fas fa-mobile-alt mr-1"></i>Celular
                            </span>
                        @else
                            <span class="ml-2 px-2 py-1 text-xs bg-green-100 text-green-800 rounded-full">
                                <i class="fas fa-headphones mr-1"></i>Accesorio
                            </span>
                        @endif
                    </div>
                    <div>
                        <span class="font-medium text-gray-700">Stock Actual:</span>
                        <span class="text-gray-900 ml-2 font-bold">{{ $producto->stock_actual }}</span>
                    </div>
                    <div>
                        <span class="font-medium text-gray-700">Creado:</span>
                        <span class="text-gray-900 ml-2">{{ $producto->created_at->format('d/m/Y') }}</span>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="bg-blue-900 px-6 py-4">
                    <h2 class="text-xl font-bold text-white">
                        <i class="fas fa-edit mr-2"></i>
                        Editar Información
                    </h2>
                </div>

                <form action="{{ route('inventario.productos.update', $producto) }}" method="POST" enctype="multipart/form-data" class="p-6">
                    @csrf
                    @method('PUT')

                    <!-- TIPO DE PRODUCTO (SOLO LECTURA) -->
                    <div class="mb-8 bg-gray-50 p-4 rounded-lg border border-gray-200">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                @if($producto->tipo_producto == 'celular')
                                    <i class="fas fa-mobile-alt text-3xl text-blue-600 mr-3"></i>
                                    <div>
                                        <p class="font-semibold text-gray-900">Tipo: Celular</p>
                                        <p class="text-sm text-gray-500">El stock se controla por IMEI individual</p>
                                    </div>
                                @else
                                    <i class="fas fa-headphones text-3xl text-green-600 mr-3"></i>
                                    <div>
                                        <p class="font-semibold text-gray-900">Tipo: Accesorio</p>
                                        <p class="text-sm text-gray-500">El stock se controla numéricamente</p>
                                    </div>
                                @endif
                            </div>
                            <span class="text-xs text-gray-400">
                                <i class="fas fa-lock mr-1"></i>No editable
                            </span>
                        </div>
                    </div>

                    <!-- INFORMACIÓN BÁSICA -->
                    <div class="mb-8">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4 pb-2 border-b border-gray-200">
                            <i class="fas fa-info-circle mr-2 text-blue-900"></i>
                            Información Básica
                        </h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="md:col-span-2">
                                <label for="nombre" class="block text-sm font-medium text-gray-700 mb-2">
                                    Nombre del Producto <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="nombre" id="nombre" value="{{ old('nombre', $producto->nombre) }}"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                       required>
                            </div>

                            <div>
                                <label for="categoria_id" class="block text-sm font-medium text-gray-700 mb-2">
                                    Categoría <span class="text-red-500">*</span>
                                </label>
                                <select name="categoria_id" id="categoria_id" 
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                        required>
                                    @foreach($categorias as $categoria)
                                        <option value="{{ $categoria->id }}" {{ old('categoria_id', $producto->categoria_id) == $categoria->id ? 'selected' : '' }}>
                                            {{ $categoria->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label for="marca" class="block text-sm font-medium text-gray-700 mb-2">Marca</label>
                                <input type="text" name="marca" id="marca" value="{{ old('marca', $producto->marca) }}"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            </div>

                            <div>
                                <label for="modelo" class="block text-sm font-medium text-gray-700 mb-2">Modelo</label>
                                <input type="text" name="modelo" id="modelo" value="{{ old('modelo', $producto->modelo) }}"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            </div>

                            <div>
                                <label for="codigo_barras" class="block text-sm font-medium text-gray-700 mb-2">Código de Barras</label>
                                <input type="text" name="codigo_barras" id="codigo_barras" value="{{ old('codigo_barras', $producto->codigo_barras) }}"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            </div>

                            <div class="md:col-span-2">
                                <label for="descripcion" class="block text-sm font-medium text-gray-700 mb-2">Descripción</label>
                                <textarea name="descripcion" id="descripcion" rows="2"
                                          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">{{ old('descripcion', $producto->descripcion) }}</textarea>
                            </div>
                        </div>
                    </div>

                    <!-- PRECIOS -->
                    <div class="mb-8">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4 pb-2 border-b border-gray-200">
                            <i class="fas fa-dollar-sign mr-2 text-blue-900"></i>
                            Precios
                        </h3>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <label for="precio_compra_actual" class="block text-sm font-medium text-gray-700 mb-2">
                                    Precio de Compra <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <span class="absolute left-3 top-2.5 text-gray-500">S/</span>
                                    <input type="number" name="precio_compra_actual" id="precio_compra_actual" 
                                           value="{{ old('precio_compra_actual', $producto->precio_compra_actual) }}" 
                                           step="0.01" min="0"
                                           class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                           required>
                                </div>
                            </div>

                            <div>
                                <label for="precio_venta" class="block text-sm font-medium text-gray-700 mb-2">
                                    Precio de Venta <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <span class="absolute left-3 top-2.5 text-gray-500">S/</span>
                                    <input type="number" name="precio_venta" id="precio_venta" 
                                           value="{{ old('precio_venta', $producto->precio_venta) }}" 
                                           step="0.01" min="0"
                                           class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                           required>
                                </div>
                            </div>

                            <div>
                                <label for="precio_mayorista" class="block text-sm font-medium text-gray-700 mb-2">Precio Mayorista</label>
                                <div class="relative">
                                    <span class="absolute left-3 top-2.5 text-gray-500">S/</span>
                                    <input type="number" name="precio_mayorista" id="precio_mayorista" 
                                           value="{{ old('precio_mayorista', $producto->precio_mayorista) }}" 
                                           step="0.01" min="0"
                                           class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                </div>
                            </div>

                            <!-- Margen de Ganancia (calculado) -->
                            <div class="md:col-span-3 bg-blue-50 p-4 rounded-lg">
                                <p class="text-sm font-medium text-blue-900">
                                    <i class="fas fa-chart-line mr-2"></i>
                                    Margen de Ganancia: <span class="text-lg font-bold">{{ number_format($producto->margen_ganancia, 2) }}%</span>
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- CONTROL DE STOCK -->
                    <div class="mb-8">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4 pb-2 border-b border-gray-200">
                            <i class="fas fa-boxes mr-2 text-blue-900"></i>
                            Control de Stock
                        </h3>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <label for="stock_minimo" class="block text-sm font-medium text-gray-700 mb-2">
                                    Stock Mínimo <span class="text-red-500">*</span>
                                </label>
                                <input type="number" name="stock_minimo" id="stock_minimo" 
                                       value="{{ old('stock_minimo', $producto->stock_minimo) }}" min="0"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                       required>
                            </div>

                            <div>
                                <label for="stock_maximo" class="block text-sm font-medium text-gray-700 mb-2">
                                    Stock Máximo <span class="text-red-500">*</span>
                                </label>
                                <input type="number" name="stock_maximo" id="stock_maximo" 
                                       value="{{ old('stock_maximo', $producto->stock_maximo) }}" min="1"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                       required>
                            </div>

                            <div>
                                <label for="ubicacion" class="block text-sm font-medium text-gray-700 mb-2">Ubicación Física</label>
                                <input type="text" name="ubicacion" id="ubicacion" 
                                       value="{{ old('ubicacion', $producto->ubicacion) }}"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            </div>
                        </div>

                        <!-- Alerta de Stock -->
                        <div class="mt-4 p-4 rounded-lg 
                            @if($producto->estado_stock == 'sin_stock') bg-red-50 border border-red-200
                            @elseif($producto->estado_stock == 'bajo') bg-yellow-50 border border-yellow-200
                            @else bg-green-50 border border-green-200
                            @endif">
                            <p class="text-sm font-medium
                                @if($producto->estado_stock == 'sin_stock') text-red-800
                                @elseif($producto->estado_stock == 'bajo') text-yellow-800
                                @else text-green-800
                                @endif">
                                <i class="fas fa-info-circle mr-2"></i>
                                Estado Actual: 
                                <strong>{{ $producto->stock_actual }} {{ $producto->unidad_medida }}</strong>
                                @if($producto->estado_stock == 'sin_stock')
                                    - Sin stock disponible
                                @elseif($producto->estado_stock == 'bajo')
                                    - Stock por debajo del mínimo
                                @else
                                    - Stock normal
                                @endif
                            </p>
                        </div>

                        <div class="mt-4 bg-yellow-50 border-l-4 border-yellow-500 p-4 rounded-lg">
                            <p class="text-sm text-yellow-700">
                                <i class="fas fa-exclamation-triangle mr-2"></i>
                                <strong>Nota:</strong> El stock NO se modifica aquí. Para ajustar stock, usa el módulo de 
                                @if($producto->tipo_producto == 'celular')
                                    <a href="{{ route('inventario.imeis.index') }}" class="underline font-semibold">Gestión de IMEIs</a>
                                @else
                                    <a href="{{ route('inventario.movimientos.create') }}" class="underline font-semibold">Movimientos de Inventario</a>
                                @endif
                            </p>
                        </div>
                    </div>

                    <!-- IMAGEN Y ESTADO -->
                    <div class="mb-8">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4 pb-2 border-b border-gray-200">
                            <i class="fas fa-image mr-2 text-blue-900"></i>
                            Imagen y Estado
                        </h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="imagen" class="block text-sm font-medium text-gray-700 mb-2">Nueva Imagen (Opcional)</label>
                                <input type="file" name="imagen" id="imagen" accept="image/*"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                       onchange="previewImage(event)">
                                <p class="mt-1 text-xs text-gray-500">JPG, PNG, WEBP (Max. 2MB)</p>
                            </div>

                            <div>
                                <label for="estado" class="block text-sm font-medium text-gray-700 mb-2">
                                    Estado <span class="text-red-500">*</span>
                                </label>
                                <select name="estado" id="estado" 
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                        required>
                                    <option value="activo" {{ old('estado', $producto->estado) == 'activo' ? 'selected' : '' }}>Activo</option>
                                    <option value="inactivo" {{ old('estado', $producto->estado) == 'inactivo' ? 'selected' : '' }}>Inactivo</option>
                                    <option value="descontinuado" {{ old('estado', $producto->estado) == 'descontinuado' ? 'selected' : '' }}>Descontinuado</option>
                                </select>
                            </div>

                            <!-- Imagen Actual -->
                            @if($producto->imagen)
                                <div class="md:col-span-2">
                                    <p class="text-sm font-medium text-gray-700 mb-2">Imagen Actual:</p>
                                    <img src="{{ $producto->imagen_url }}" alt="{{ $producto->nombre }}" 
                                         class="h-32 rounded-lg border border-gray-300">
                                </div>
                            @endif

                            <!-- Preview Nueva -->
                            <div id="imagePreviewContainer" class="md:col-span-2 hidden">
                                <p class="text-sm font-medium text-gray-700 mb-2">Nueva Imagen:</p>
                                <img id="imagePreview" src="" alt="Preview" class="h-32 rounded-lg border border-gray-300">
                            </div>
                        </div>
                    </div>

                    <!-- Botones -->
                    <div class="flex items-center justify-end space-x-3 pt-6 border-t border-gray-200">
                        <a href="{{ route('inventario.productos.index') }}" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                            <i class="fas fa-times mr-2"></i>Cancelar
                        </a>
                        <button type="submit" class="px-6 py-2 bg-blue-900 text-white rounded-lg hover:bg-blue-800">
                            <i class="fas fa-save mr-2"></i>Actualizar Producto
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function previewImage(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.getElementById('imagePreview');
                    const container = document.getElementById('imagePreviewContainer');
                    preview.src = e.target.result;
                    container.classList.remove('hidden');
                };
                reader.readAsDataURL(file);
            }
        }
    </script>
</body>
</html>