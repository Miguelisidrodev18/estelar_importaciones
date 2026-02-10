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
        <x-header 
            title="Editar Producto" 
            subtitle="Actualiza la información de {{ $producto->nombre }}" 
        />

        <div class="max-w-5xl mx-auto">
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="bg-blue-900 px-6 py-4">
                    <h2 class="text-xl font-bold text-white">
                        <i class="fas fa-edit mr-2"></i>
                        Editar Información del Producto
                    </h2>
                </div>

                <form action="{{ route('inventario.productos.update', $producto) }}" method="POST" enctype="multipart/form-data" class="p-6">
                    @csrf
                    @method('PUT')

                    <!-- Código (solo lectura) -->
                    <div class="mb-6 bg-gray-50 p-4 rounded-lg border border-gray-200">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                            <div>
                                <span class="font-medium text-gray-700">Código:</span>
                                <span class="text-gray-900 ml-2">{{ $producto->codigo }}</span>
                            </div>
                            <div>
                                <span class="font-medium text-gray-700">Stock Actual:</span>
                                <span class="text-gray-900 ml-2">{{ $producto->stock_actual }} {{ $producto->unidad_medida }}</span>
                            </div>
                            <div>
                                <span class="font-medium text-gray-700">Creado:</span>
                                <span class="text-gray-900 ml-2">{{ $producto->created_at->format('d/m/Y') }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Información Básica -->
                    <div class="mb-8">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4 pb-2 border-b border-gray-200">
                            <i class="fas fa-info-circle mr-2 text-blue-900"></i>
                            Información Básica
                        </h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Nombre -->
                            <div>
                                <label for="nombre" class="block text-sm font-medium text-gray-700 mb-2">
                                    Nombre del Producto <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="nombre" id="nombre" value="{{ old('nombre', $producto->nombre) }}"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 @error('nombre') border-red-500 @enderror"
                                       required>
                                @error('nombre')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Categoría -->
                            <div>
                                <label for="categoria_id" class="block text-sm font-medium text-gray-700 mb-2">
                                    Categoría <span class="text-red-500">*</span>
                                </label>
                                <select name="categoria_id" id="categoria_id" 
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 @error('categoria_id') border-red-500 @enderror"
                                        required>
                                    @foreach($categorias as $categoria)
                                        <option value="{{ $categoria->id }}" {{ old('categoria_id', $producto->categoria_id) == $categoria->id ? 'selected' : '' }}>
                                            {{ $categoria->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('categoria_id')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Marca -->
                            <div>
                                <label for="marca" class="block text-sm font-medium text-gray-700 mb-2">Marca</label>
                                <input type="text" name="marca" id="marca" value="{{ old('marca', $producto->marca) }}"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            </div>

                            <!-- Modelo -->
                            <div>
                                <label for="modelo" class="block text-sm font-medium text-gray-700 mb-2">Modelo</label>
                                <input type="text" name="modelo" id="modelo" value="{{ old('modelo', $producto->modelo) }}"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            </div> 

                            <!-- Unidad de Medida -->
                            <div>
                                <label for="unidad_medida" class="block text-sm font-medium text-gray-700 mb-2">
                                    Unidad de Medida <span class="text-red-500">*</span>
                                </label>
                                <select name="unidad_medida" id="unidad_medida" 
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                        required>
                                    <option value="unidad" {{ old('unidad_medida', $producto->unidad_medida) == 'unidad' ? 'selected' : '' }}>Unidad</option>
                                    <option value="kg" {{ old('unidad_medida', $producto->unidad_medida) == 'kg' ? 'selected' : '' }}>Kilogramo (kg)</option>
                                    <option value="litro" {{ old('unidad_medida', $producto->unidad_medida) == 'litro' ? 'selected' : '' }}>Litro</option>
                                    <option value="caja" {{ old('unidad_medida', $producto->unidad_medida) == 'caja' ? 'selected' : '' }}>Caja</option>
                                    <option value="paquete" {{ old('unidad_medida', $producto->unidad_medida) == 'paquete' ? 'selected' : '' }}>Paquete</option>
                                    <option value="metro" {{ old('unidad_medida', $producto->unidad_medida) == 'metro' ? 'selected' : '' }}>Metro</option>
                                </select>
                            </div>

                            <!-- Código de Barras -->
                            <div>
                                <label for="codigo_barras" class="block text-sm font-medium text-gray-700 mb-2">Código de Barras</label>
                                <input type="text" name="codigo_barras" id="codigo_barras" value="{{ old('codigo_barras', $producto->codigo_barras) }}"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 @error('codigo_barras') border-red-500 @enderror">
                                @error('codigo_barras')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Descripción -->
                        <div class="mt-6">
                            <label for="descripcion" class="block text-sm font-medium text-gray-700 mb-2">Descripción</label>
                            <textarea name="descripcion" id="descripcion" rows="3"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">{{ old('descripcion', $producto->descripcion) }}</textarea>
                        </div>
                    </div>

                    <!-- Precios -->
                    <div class="mb-8">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4 pb-2 border-b border-gray-200">
                            <i class="fas fa-dollar-sign mr-2 text-blue-900"></i>
                            Precios
                        </h3>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <!-- Precio de Compra -->
                            <div>
                                <label for="precio_compra_actual" class="block text-sm font-medium text-gray-700 mb-2">
                                    Precio de Compra <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <span class="absolute left-3 top-2 text-gray-500">S/</span>
                                    <input type="number" step="0.01" min="0" name="precio_compra_actual" id="precio_compra_actual" 
                                            value="{{ old('precio_compra_actual', $producto->precio_compra_actual) }}"
                                            class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                            required>
                                </div>
                            </div>

                            <!-- Precio de Venta -->
                            <div>
                                <label for="precio_venta" class="block text-sm font-medium text-gray-700 mb-2">
                                    Precio de Venta <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <span class="absolute left-3 top-2 text-gray-500">S/</span>
                                    <input type="number" step="0.01" min="0" name="precio_venta" id="precio_venta" 
                                            value="{{ old('precio_venta', $producto->precio_venta) }}"
                                            class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                            required>
                                </div>
                                <p class="mt-1 text-xs text-gray-500">Margen: {{ $producto->margen_ganancia }}%</p>
                            </div>

                            <!-- Precio Mayorista -->
                            <div>
                                <label for="precio_mayorista" class="block text-sm font-medium text-gray-700 mb-2">Precio Mayorista</label>
                                <div class="relative">
                                    <span class="absolute left-3 top-2 text-gray-500">S/</span>
                                    <input type="number" step="0.01" min="0" name="precio_mayorista" id="precio_mayorista" 
                                            value="{{ old('precio_mayorista', $producto->precio_mayorista) }}"
                                            class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Stock -->
                    <div class="mb-8">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4 pb-2 border-b border-gray-200">
                            <i class="fas fa-boxes mr-2 text-blue-900"></i>
                            Control de Stock
                        </h3>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <!-- Stock Mínimo -->
                            <div>
                                <label for="stock_minimo" class="block text-sm font-medium text-gray-700 mb-2">
                                    Stock Mínimo <span class="text-red-500">*</span>
                                </label>
                                <input type="number" min="0" name="stock_minimo" id="stock_minimo" 
                                       value="{{ old('stock_minimo', $producto->stock_minimo) }}"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                       required>
                            </div>

                            <!-- Stock Máximo -->
                            <div>
                                <label for="stock_maximo" class="block text-sm font-medium text-gray-700 mb-2">
                                    Stock Máximo <span class="text-red-500">*</span>
                                </label>
                                <input type="number" min="1" name="stock_maximo" id="stock_maximo" 
                                       value="{{ old('stock_maximo', $producto->stock_maximo) }}"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                       required>
                            </div>

                            <!-- Ubicación -->
                            <div>
                                <label for="ubicacion" class="block text-sm font-medium text-gray-700 mb-2">Ubicación en Almacén</label>
                                <input type="text" name="ubicacion" id="ubicacion" value="{{ old('ubicacion', $producto->ubicacion) }}"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                       placeholder="Ej: Pasillo A - Estante 3">
                            </div>
                        </div>

                        <!-- Alerta de stock -->
                        <div class="mt-6 p-4 rounded-lg border
                            @if($producto->estado_stock == 'sin_stock') bg-red-50 border-red-200
                            @elseif($producto->estado_stock == 'bajo') bg-yellow-50 border-yellow-200
                            @else bg-green-50 border-green-200
                            @endif">
                            <p class="text-sm font-medium
                                @if($producto->estado_stock == 'sin_stock') text-red-700
                                @elseif($producto->estado_stock == 'bajo') text-yellow-700
                                @else text-green-700
                                @endif">
                                <i class="fas fa-info-circle mr-2"></i>
                                Stock actual: {{ $producto->stock_actual }} {{ $producto->unidad_medida }}
                                @if($producto->estado_stock == 'sin_stock')
                                    - ¡SIN STOCK!
                                @elseif($producto->estado_stock == 'bajo')
                                    - Stock bajo (alertar)
                                @else
                                    - Stock normal
                                @endif
                            </p>
                        </div>
                    </div>

                    <!-- Imagen y Estado -->
                    <div class="mb-8">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4 pb-2 border-b border-gray-200">
                            <i class="fas fa-image mr-2 text-blue-900"></i>
                            Imagen y Estado
                        </h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Imagen -->
                            <div>
                                <label for="imagen" class="block text-sm font-medium text-gray-700 mb-2">Imagen del Producto</label>
                                <div class="flex items-center space-x-4">
                                    @if($producto->imagen)
                                        <div id="current-image">
                                            <img src="{{ $producto->imagen_url }}" alt="{{ $producto->nombre }}" class="h-32 w-32 object-cover rounded-lg border-2 border-gray-300">
                                            <p class="text-xs text-gray-500 mt-1 text-center">Imagen actual</p>
                                        </div>
                                    @endif
                                    
                                    <div id="preview-container" class="hidden">
                                        <img id="preview-image" src="" alt="Vista previa" class="h-32 w-32 object-cover rounded-lg border-2 border-blue-500">
                                        <p class="text-xs text-blue-600 mt-1 text-center">Nueva imagen</p>
                                    </div>
                                    
                                    <div class="flex-1">
                                        <input type="file" name="imagen" id="imagen" accept="image/*"
                                                class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
                                                onchange="previewImage(event)">
                                        <p class="mt-1 text-xs text-gray-500">JPG, JPEG, PNG, WEBP. Máx 2MB</p>
                                        @if($producto->imagen)
                                            <p class="mt-1 text-xs text-gray-500">Dejar vacío para mantener la imagen actual</p>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <!-- Estado -->
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
                        </div>
                    </div>

                    <!-- Nota informativa -->
                    <div class="mb-6 bg-yellow-50 border-l-4 border-yellow-500 p-4 rounded-lg">
                        <div class="flex">
                            <i class="fas fa-exclamation-triangle text-yellow-500 mt-0.5 mr-3"></i>
                            <div class="text-sm text-yellow-700">
                                <p class="font-medium">Importante:</p>
                                <p class="mt-1">El stock actual NO se modifica desde aquí. Para ajustar el stock, usa la sección de Movimientos de Inventario.</p>
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
            const input = event.target;
            const preview = document.getElementById('preview-image');
            const container = document.getElementById('preview-container');
            
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    container.classList.remove('hidden');
                }
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
</body>
</html>