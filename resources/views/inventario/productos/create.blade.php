<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuevo Producto - CORPORACIÓN ADIVON SAC</title>
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50">
    <x-sidebar :role="auth()->user()->role->nombre" />

    <div class="ml-64 p-8">
        <x-header title="Nuevo Producto" subtitle="Registra un nuevo producto en el inventario" />

        <div class="max-w-5xl mx-auto">
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="bg-blue-900 px-6 py-4">
                    <h2 class="text-xl font-bold text-white">
                        <i class="fas fa-box mr-2"></i>
                        Información del Producto
                    </h2>
                </div>

                <form action="{{ route('inventario.productos.store') }}" method="POST" enctype="multipart/form-data" class="p-6">
                    @csrf

<!-- SECCIÓN 1: TIPO DE PRODUCTO -->
    <div class="mb-8">
        <h3 class="text-lg font-semibold text-gray-900 mb-4 pb-2 border-b border-gray-200">
        <i class="fas fa-mobile-alt mr-2 text-blue-900"></i>
        Tipo de Producto
        </h3>

    <div class="grid grid-cols-2 gap-4">
        <!-- CELULAR -->
        <label class="cursor-pointer">
            <input type="radio" 
                    name="tipo_producto" 
                    value="celular" 
                    id="tipo_celular"
                    class="peer hidden" 
                    {{ old('tipo_producto') == 'celular' ? 'checked' : '' }}
                    required>
            <div class="border-2 border-gray-300 rounded-lg p-6 text-center hover:border-blue-500 peer-checked:border-blue-500 peer-checked:bg-blue-50 transition-all">
                <i class="fas fa-mobile-alt text-5xl text-blue-600 mb-3"></i>
                <p class="text-lg font-semibold text-gray-900">Celular</p>
                <p class="text-sm text-gray-500 mt-2">Requiere IMEI único</p>
            </div>
        </label>

        <!-- ACCESORIO -->
        <label class="cursor-pointer">
            <input type="radio" 
                    name="tipo_producto" 
                    value="accesorio" 
                    id="tipo_accesorio"
                    class="peer hidden" 
                    {{ old('tipo_producto', 'accesorio') == 'accesorio' ? 'checked' : '' }}>
                <div class="border-2 border-gray-300 rounded-lg p-6 text-center hover:border-green-500 peer-checked:border-green-500 peer-checked:bg-green-50 transition-all">
                    <i class="fas fa-headphones text-5xl text-green-600 mb-3"></i>
                    <p class="text-lg font-semibold text-gray-900">Accesorio</p>
                    <p class="text-sm text-gray-500 mt-2">Stock numérico</p>
                </div>
            </label>
        </div>

    @error('tipo_producto')
        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>
```

                    <!-- SECCIÓN 2: INFORMACIÓN BÁSICA -->
                    <div class="mb-8">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4 pb-2 border-b border-gray-200">
                            <i class="fas fa-info-circle mr-2 text-blue-900"></i>
                            Información Básica
                        </h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Nombre -->
                            <div class="md:col-span-2">
                                <label for="nombre" class="block text-sm font-medium text-gray-700 mb-2">
                                    Nombre del Producto <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="nombre" id="nombre" value="{{ old('nombre') }}"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
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
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                        required>
                                    <option value="">Seleccione una categoría</option>
                                    @foreach($categorias as $categoria)
                                        <option value="{{ $categoria->id }}" {{ old('categoria_id') == $categoria->id ? 'selected' : '' }}>
                                            {{ $categoria->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Marca -->
                            <div>
                                <label for="marca" class="block text-sm font-medium text-gray-700 mb-2">Marca</label>
                                <input type="text" name="marca" id="marca" value="{{ old('marca') }}"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                       placeholder="Ej: Samsung, Apple, Xiaomi">
                            </div>

                            <!-- Modelo -->
                            <div>
                                <label for="modelo" class="block text-sm font-medium text-gray-700 mb-2">Modelo</label>
                                <input type="text" name="modelo" id="modelo" value="{{ old('modelo') }}"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                       placeholder="Ej: Galaxy S23, iPhone 15">
                            </div>

                            <!-- Unidad de Medida -->
                            <div>
                                <label for="unidad_medida" class="block text-sm font-medium text-gray-700 mb-2">
                                    Unidad de Medida <span class="text-red-500">*</span>
                                </label>
                                <select name="unidad_medida" id="unidad_medida" 
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                        required>
                                    <option value="unidad" selected>Unidad</option>
                                    <option value="caja">Caja</option>
                                    <option value="paquete">Paquete</option>
                                </select>
                            </div>

                            <!-- Código de Barras -->
                            <div>
                                <label for="codigo_barras" class="block text-sm font-medium text-gray-700 mb-2">Código de Barras</label>
                                <input type="text" name="codigo_barras" id="codigo_barras" value="{{ old('codigo_barras') }}"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                       placeholder="Código único del producto">
                            </div>

                            <!-- Descripción -->
                            <div class="md:col-span-2">
                                <label for="descripcion" class="block text-sm font-medium text-gray-700 mb-2">Descripción</label>
                                <textarea name="descripcion" id="descripcion" rows="2"
                                          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                          placeholder="Descripción detallada del producto">{{ old('descripcion') }}</textarea>
                            </div>
                        </div>
                    </div>

                    <!-- SECCIÓN 3: PRECIOS -->
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
                                           value="{{ old('precio_compra_actual') }}" step="0.01" min="0"
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
                                           value="{{ old('precio_venta') }}" step="0.01" min="0"
                                           class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                           required>
                                </div>
                            </div>

                            <div>
                                <label for="precio_mayorista" class="block text-sm font-medium text-gray-700 mb-2">Precio Mayorista</label>
                                <div class="relative">
                                    <span class="absolute left-3 top-2.5 text-gray-500">S/</span>
                                    <input type="number" name="precio_mayorista" id="precio_mayorista" 
                                           value="{{ old('precio_mayorista') }}" step="0.01" min="0"
                                           class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- SECCIÓN 4: STOCK (SOLO PARA ACCESORIOS) -->
                    <div id="stockSection" class="mb-8">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4 pb-2 border-b border-gray-200">
                            <i class="fas fa-boxes mr-2 text-blue-900"></i>
                            Control de Stock
                        </h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="stock_minimo" class="block text-sm font-medium text-gray-700 mb-2">
                                    Stock Mínimo <span class="text-red-500">*</span>
                                </label>
                                <input type="number" name="stock_minimo" id="stock_minimo" value="{{ old('stock_minimo', 10) }}" min="0"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                       required>
                            </div>

                            <div>
                                <label for="stock_maximo" class="block text-sm font-medium text-gray-700 mb-2">
                                    Stock Máximo <span class="text-red-500">*</span>
                                </label>
                                <input type="number" name="stock_maximo" id="stock_maximo" value="{{ old('stock_maximo', 1000) }}" min="1"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                       required>
                            </div>

                            <div>
                                <label for="ubicacion" class="block text-sm font-medium text-gray-700 mb-2">Ubicación Física</label>
                                <input type="text" name="ubicacion" id="ubicacion" value="{{ old('ubicacion') }}"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                       placeholder="Ej: Estante A-5">
                            </div>

                            <!-- Stock Inicial (SOLO ACCESORIOS) -->
                            <div id="stockInicialDiv">
                                <label for="stock_inicial" class="block text-sm font-medium text-gray-700 mb-2">Stock Inicial (Opcional)</label>
                                <input type="number" name="stock_inicial" id="stock_inicial" value="{{ old('stock_inicial') }}" min="0"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                       placeholder="Cantidad inicial">
                            </div>

                            <!-- Almacén (si hay stock inicial) -->
                            <div id="almacenInicialDiv" class="md:col-span-2">
                                <label for="almacen_id" class="block text-sm font-medium text-gray-700 mb-2">Almacén para Stock Inicial</label>
                                <select name="almacen_id" id="almacen_id" 
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                    <option value="">Seleccione un almacén</option>
                                    @foreach($almacenes as $almacen)
                                        <option value="{{ $almacen->id }}">{{ $almacen->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="mt-4 bg-yellow-50 border-l-4 border-yellow-500 p-4 rounded-lg">
                            <p class="text-sm text-yellow-700">
                                <i class="fas fa-info-circle mr-2"></i>
                                <strong>Nota:</strong> Para celulares, el stock se controlará mediante registros IMEI individuales en el módulo de movimientos.
                            </p>
                        </div>
                    </div>

                    <!-- SECCIÓN 5: IMAGEN -->
                    <div class="mb-8">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4 pb-2 border-b border-gray-200">
                            <i class="fas fa-image mr-2 text-blue-900"></i>
                            Imagen del Producto
                        </h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="imagen" class="block text-sm font-medium text-gray-700 mb-2">Subir Imagen</label>
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
                                    <option value="activo" selected>Activo</option>
                                    <option value="inactivo">Inactivo</option>
                                    <option value="descontinuado">Descontinuado</option>
                                </select>
                            </div>

                            <div id="imagePreviewContainer" class="md:col-span-2 hidden">
                                <p class="text-sm font-medium text-gray-700 mb-2">Vista Previa:</p>
                                <img id="imagePreview" src="" alt="Preview" class="max-h-48 rounded-lg border border-gray-300">
                            </div>
                        </div>
                    </div>

                    <!-- Botones -->
                    <div class="flex items-center justify-end space-x-3 pt-6 border-t border-gray-200">
                        <a href="{{ route('inventario.productos.index') }}" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                            <i class="fas fa-times mr-2"></i>Cancelar
                        </a>
                        <button type="submit" class="px-6 py-2 bg-blue-900 text-white rounded-lg hover:bg-blue-800">
                            <i class="fas fa-save mr-2"></i>Guardar Producto
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

<script>
// Función para alternar campos según tipo de producto
function toggleIMEIFields() {
    const tipoSeleccionado = document.querySelector('input[name="tipo_producto"]:checked');
    
    if (!tipoSeleccionado) return;
    
    const tipo = tipoSeleccionado.value;
    const stockInicialDiv = document.getElementById('stockInicialDiv');
    const almacenInicialDiv = document.getElementById('almacenInicialDiv');
    
    console.log('Tipo seleccionado:', tipo);
    
    if (tipo === 'celular') {
        if (stockInicialDiv) stockInicialDiv.style.display = 'none';
        if (almacenInicialDiv) almacenInicialDiv.style.display = 'none';
    } else {
        if (stockInicialDiv) stockInicialDiv.style.display = 'block';
        if (almacenInicialDiv) almacenInicialDiv.style.display = 'block';
    }
}

// Preview de imagen
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

// Inicializar cuando cargue la página
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM Cargado');
    
    toggleIMEIFields();
    
    const radioButtons = document.querySelectorAll('input[name="tipo_producto"]');
    radioButtons.forEach(radio => {
        radio.addEventListener('change', function() {
            console.log('Radio cambiado a:', this.value);
            toggleIMEIFields();
        });
    });
    
    // SOLUCIÓN: Interceptar envío y FORZAR el valor correcto
    const form = document.querySelector('form');
    form.addEventListener('submit', function(e) {
        // Obtener el radio button marcado
        const radioChecked = document.querySelector('input[name="tipo_producto"]:checked');
        
        if (!radioChecked) {
            e.preventDefault();
            alert('⚠️ Debes seleccionar un tipo de producto (Celular o Accesorio)');
            return false;
        }
        
        // ELIMINAR todos los inputs tipo_producto anteriores
        const inputsAntiguos = form.querySelectorAll('input[name="tipo_producto"]:not([type="radio"])');
        inputsAntiguos.forEach(input => input.remove());
        
        // CREAR un input hidden con el valor CORRECTO
        const hiddenInput = document.createElement('input');
        hiddenInput.type = 'hidden';
        hiddenInput.name = 'tipo_producto';
        hiddenInput.value = radioChecked.value;
        form.appendChild(hiddenInput);
        
        console.log('✅ Tipo de producto que se enviará:', radioChecked.value);
        
        // Permitir que el formulario se envíe
        // El input hidden sobrescribirá cualquier otro valor
    });
});
</script>
</body>
</html>